# Behavioral security tests

These framework-free tests encode the V1 state-machine and authorization contracts so CI can execute them without WordPress/WooCommerce.

They are contract tests, not a replacement for real integration tests against a WordPress test instance. The next layer should exercise the actual REST controllers with WordPress/WooCommerce test doubles and verify the same invariants at the HTTP boundary.
