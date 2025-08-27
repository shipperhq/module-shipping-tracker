# ShipperHQ Shipping Tracker

A simple extension which allows you to enter custom shipment tracking URLs. This will enable your customers to have a clickable shipping tracking link in the new shipment email and when viewing their order in Magento.

---

## Features

- **Custom Tracking URLs**: Define generic tracking URL patterns for your shipments.
- **Multiple Trackers**: Configure up to five independent trackers (Tracker 1…5).
- **Email and Account Links**: Adds clickable tracking links to shipment emails and the order view in My Account.
- **Carrier-Agnostic**: Works with any carrier that provides a public tracking URL.
- **Per-Store Configuration**: Scope settings at website/store view for multi-store setups.
- **Magento 2 Compatible**: Tested with Magento 2.4.4+.

---

## Installation

Install using composer by adding to your composer file using commands:

1. composer require shipperhq/module-shipping-tracker
2. composer update
3. bin/magento setup:upgrade

For up-to-date installation instructions, use the composer commands above.

## Requirements

- Magento 2.4.4+
    - Compatibility with earlier editions is possible but not maintained
    - Supports both Magento Opensource (Community) and Magento Commerce (Enterprise)

## Configuration

The shipping tracker configuration can be found under: Stores > Configuration > Sales > Shipping Methods > ShipperHQ Tracker 1...5

1. Enable one of the trackers (Tracker 1…5).
2. Set a descriptive Title (shown to customers).
3. Choose a Predefined Carrier (Pre URL) or select "Use Manual Url" to provide your own.
4. If using Manual Url, enter a Tracking URL pattern using the placeholders below.

Placeholders supported in the Tracking URL pattern:

- #TRACKNUM#: Replaced with the package tracking number. Include this exactly where the carrier expects the tracking value.
- #POSTCODE#: Replaced with the destination postcode when available. Only include if required by the carrier’s tracking page. If the postcode is not available, the token will not be replaced.
- #SPECIAL#: Add this token when you need to link to a generic tracking page without auto-inserting a tracking number. When present, the module removes #SPECIAL# and also strips any #TRACKNUM# token from the URL, resulting in a general tracking page link.

Examples (Manual Url):

- Tracking number only: `https://carrier.example/track?num=#TRACKNUM#`
- Tracking number + postcode: `https://carrier.example/track?num=#TRACKNUM#&postcode=#POSTCODE#`
- Generic tracking page (no auto-insert): `https://carrier.example/track#SPECIAL#`

## Support

If you have any issues with this extension, open an issue on [GitHub](https://github.com/shipperhq/module-shipping-tracker/issues).

## Contribution

Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

## License

Copyright (c) 2015 Zowta LLC & Zowta Ltd. See [license] for details.

We also dutifully respect the [Magento] OSL license, which is included in this codebase.

[license]: LICENSE.txt
[magento]: https://github.com/magento/magento2/blob/2.4-develop/LICENSE.txt

## Copyright

Copyright (c) 2016 Zowta LLC & Zowta Ltd.

