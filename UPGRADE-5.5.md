# CHANGELOG for Shopware 5.5.x

This changelog references changes done in Shopware 5.4 patch versions.

[View all changes from v5.4.0...v5.5.0](https://github.com/shopware/shopware/compare/v5.4.0...v5.5.0)

### Changes

* Changed the execution model of `replace` hooks to prevent multiple calls of the hooked method, if more than one `replace` hook on the same method exists and all of them call `executeParent()` once
* Changed Symfony version to 3.4.4
* Changed the event `Shopware_Form_Builder` so that the `reference` contains the `BlockPrefix` of the Formtype, not the name
