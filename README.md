# VATSIM Connect Provider for OAuth 2.0 Client

[![Build Status](https://img.shields.io/github/actions/workflow/status/vatsimnetwork/oauth2-vatsim-php/test.yaml?branch=main)](https://github.com/vatsimnetwork/oauth2-vatsim-php/actions/workflows/test.yaml)
[![License](https://img.shields.io/packagist/l/vatsim/oauth2-vatsim)](https://github.com/vatsimnetwork/oauth2-vatsim-php/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/vatsim/oauth2-vatsim)](https://packagist.org/packages/vatsim/oauth2-vatsim)

This package provides VATSIM Connect support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Requirements

To use this package, it will be necessary to have a VATSIM Connect client ID and client secret.

Please follow the [VATSIM Connect instructions][oauth-setup] to create the required credentials.

[oauth-setup]: https://github.com/vatsimnetwork/developer-info/wiki/Connect

## Installation

To install, use composer:

```sh
composer require vatsim/oauth2-vatsim
```

## Usage

Usage is the same as [The League's OAuth client][league-usage], using `\Vatsim\OAuth2\Client\Provider\Vatsim` as the provider.

[league-usage]: https://oauth2-client.thephpleague.com/usage/

### Available Options

The `Vatsim` provider has the following options:

- `domain` allows for switching between the prod or dev Connect instance (default: `https://auth.vatsim.net`)

### Scopes

At time of writing, [available scopes][scopes] are:
- `full_name` - Full name (first and last)
- `email`- Email address
- `country` - Residence country
- `vatsim_details` - VATSIM pilot rating, ATC rating, region, division, sub-division

[scopes]: https://github.com/vatsimnetwork/developer-info/wiki/Connect-Redirect-the-User

## Testing

Tests can be run with:

```sh
composer test
```

Style checks can be run with:

```sh
composer lint
```

## Contributing

Please see [CONTRIBUTING.md](https://github.com/vatsimnetwork/oauth2-vatsim-php/blob/main/CONTRIBUTING.md) for details.


## Credits

- [William McKinnerney](https://williammck.net)
- [All Contributors](https://github.com/vatsimnetwork/oauth2-vatsim-php/contributors)


## License

The MIT License (MIT). Please see the [License File](https://github.com/vatsimnetwork/oauth2-vatsim-php/blob/main/LICENSE) for more information.
