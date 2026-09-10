<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class TrialService
{
    private const REJECTED_META = '_woogit_trial_rejected';
    private const CLAIMED_META = '_woogit_trial_claimed';

    public function registerHooks(): void
    {
        add_action('milo_subscriptions_subscription_created', [$this, 'onSubscriptionCreated'], 19, 2);
        add_action('milo_subscriptions_subscription_manually_created', [$this, 'onSubscriptionCreated'], 19, 1);
        add_action('milo_subscriptions_subscription_status_updated', [$this, 'onStatusUpdated'], 19, 3);
        add_action('milo_subscriptions_trial_ended', [$this, 'onTrialEnded'], 19, 1);
    }

    public function onSubscriptionCreated($subscription, $order = null): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_id')) return;
        $parentOrder = $this->parentOrder($subscription, $order);
        if (!$parentOrder) return;
        $accountId = (int)$parentOrder->get_meta('_woogit_account_id');
        $siteId = (int)$parentOrder->get_meta('_woogit_site_id');
        if ($accountId <= 0 || $siteId <= 0 || !$this->isTrialSubscription($subscription, $parentOrder)) return;

        $entitlements = new EntitlementService();
        if ($entitlements->claimTrial($accountId)) {
            if (method_exists($subscription, 'update_meta_data')) $subscription->update_meta_data(self::CLAIMED_META, 'yes');
            if (method_exists($subscription, 'save')) $subscription->save();
            return;
        }

        $snapshot = $this->snapshotEntitlement($accountId, $siteId);
        if (method_exists($subscription, 'update_meta_data')) $subscription->update_meta_data(self::REJECTED_META, 'yes');
        if (method_exists($subscription, 'save')) $subscription->save();

        if (method_exists($subscription, 'update_status')) {
            $subscription->update_status('cancelled', 'WooGit: trial already used by this account.');
        } elseif (method_exists($subscription, 'set_status')) {
            $subscription->set_status('cancelled');
            if (method_exists($subscription, 'save')) $subscription->save();
        }

        if ($snapshot) $this->restoreEntitlement($snapshot);
    }

    public function onStatusUpdated($subscription, string $newStatus, string $oldStatus = ''): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_meta')) return;
        if ((string)$subscription->get_meta(self::REJECTED_META) === 'yes') return;
        $status = strtolower(trim($newStatus));
        if (!in_array($status, ['on-hold', 'cancelled', 'expired'], true)) return;
        $parentOrder = $this->parentOrder($subscription);
        if (!$parentOrder) return;
        $accountId = (int)$parentOrder->get_meta('_woogit_account_id');
        $siteId = (int)$parentOrder->get_meta('_woogit_site_id');
        if ($accountId <= 0 || $siteId <= 0) return;
        $this->deactivate($accountId, $siteId);
    }

    public function onTrialEnded($subscription): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_status')) return;
        $status = strtolower((string)$subscription->get_status());
        if (in_array($status, ['active', 'pending-cancel'], true)) return;
        $this->onStatusUpdated($subscription, $status, 'active');
    }

    private function isTrialSubscription($subscription, $order): bool
    {
        if (method_exists($subscription, 'get_time')) {
            $trialEnd = (int)$subscription->get_time('trial_end');
            if ($trialEnd > time()) return true;
        }
        foreach ((array)$order->get_items('line_item') as $item) {
            $product = method_exists($item, 'get_product') ? $item->get_product() : null;
            if ($product && $this->productHasTrial($product)) return true;
        }
        return false;
    }

    private function productHasTrial($product): bool
    {
        if ((int)$product->get_meta('_subscription_trial_length') > 0) return true;
        if (method_exists($product, 'get_parent_id') && (int)$product->get_parent_id() > 0 && function_exists('wc_get_product')) {
            $parent = wc_get_product((int)$product->get_parent_id());
            return $parent ? (int)$parent->get_meta('_subscription_trial_length') > 0 : false;
        }
        return false;
    }

    private function parentOrder($subscription, $fallback = null)
    {
        if ($fallback && is_object($fallback) && method_exists($fallback, 'get_id')) return $fallback;
        if (!is_object($subscription)) return null;
        $parentId = method_exists($subscription, 'get_parent_id') ? (int)$subscription->get_parent_id() : 0;
        if ($parentId <= 0 && method_exists($subscription, 'get_parent_order_id')) $parentId = (int)$subscription->get_parent_order_id();
        return $parentId > 0 && function_exists('wc_get_order') ? (wc_get_order($parentId) ?: null) : null;
    }

    private function snapshotEntitlement(int $accountId, int $siteId): ?array
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_entitlements';
        $row=$wpdb->get_row($wpdb->prepare("SELECT id,account_id,site_id,status,starts_at,expires_at,capabilities,created_at,updated_at FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId),ARRAY_A);
        return is_array($row) ? $row : null;
    }

    private function restoreEntitlement(array $row): void
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_entitlements';
        $data=['account_id'=>(int)$row['account_id'],'site_id'=>(int)$row['site_id'],'status'=>(string)$row['status'],'starts_at'=>(string)$row['starts_at'],'expires_at'=>$row['expires_at'],'capabilities'=>(string)$row['capabilities'],'created_at'=>(string)$row['created_at'],'updated_at'=>(string)$row['updated_at']];
        $wpdb->update($table,$data,['id'=>(int)$row['id']],['%d','%d','%s','%s','%s','%s','%s','%s'],['%d']);
    }

    private function deactivate(int $accountId, int $siteId): void
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_entitlements';
        $wpdb->update($table,['status'=>'inactive','updated_at'=>gmdate('Y-m-d H:i:s')],['account_id'=>$accountId,'site_id'=>$siteId],['%s','%s'],['%d','%d']);
    }
}
