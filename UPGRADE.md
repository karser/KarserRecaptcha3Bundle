# Upgrade Guide

## Upgrade to 0.3.x (Symfony 6.4, 7.x, 8.x support)

### Breaking Changes

#### Minimum Symfony Version
- The minimum supported Symfony version is now **6.4**
- Symfony 3.x, 4.x, and 5.x are no longer supported
- If you need support for older Symfony versions, please use version 0.2.x

#### PHP Version Requirements
- PHP 7.4 or higher is still required
- Tested with PHP 8.0, 8.1, 8.2, 8.3, and 8.5

#### Removed Backwards Compatibility Code
- Removed TreeBuilder BC layer for Symfony < 4.2
- The deprecated `TreeBuilder::root()` method usage has been removed
- Now uses the modern `TreeBuilder` constructor with the root name parameter

### New Features
- Full compatibility with Symfony 8.0
- Full compatibility with Symfony 7.x (7.0, 7.1, 7.2, 7.3, 7.4)
- Improved PHPStan compliance (level 5 with strict rules)
- Updated to PHPStan 2.x
- Updated constraint handling for Symfony 6.4+ validation changes

### Code Changes

#### Strict Type Comparisons
- Changed loose comparisons (`==`, `!=`) to strict comparisons (`===`, `!==`) in:
  - `ReCaptcha/RequestMethod/Socket.php`
  - `ReCaptcha/Response.php`

#### Updated Type Declarations
- Added return type declarations to test classes for Symfony 8 compatibility
- Updated `Recaptcha3` constraint to properly handle both array-based and named-parameter instantiation

#### Configuration
- Configuration handling remains backward compatible
- No changes required to existing configuration files

### Migration Path

If you are upgrading from an earlier version:

1. Update your `composer.json`:
   ```json
   {
       "require": {
           "karser/karser-recaptcha3-bundle": "^0.3"
       }
   }
   ```

2. Run `composer update karser/karser-recaptcha3-bundle`

3. If you were using Symfony < 6.4, you will need to upgrade your Symfony installation first

4. No code changes should be necessary in your application code

### Testing
- All existing tests pass with Symfony 6.4, 7.x, and 8.0
- PHPStan analysis passes without errors
