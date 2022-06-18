# Statamic Vitals
Provide vitals of your Statamic installation for external usage.
```json
{
	"system": {
		"name": "System name",
		"domain": "https:\/\/site.tld",
		"environment": "production",
		"laravel": "9.6.0",
		"php": "8.1.4"
	},
	"statamic": {
		"version": "3.3.4",
		"latest_version": "3.3.5",
		"pro": true,
		"antlers_version": "runtime",
		"update_available": true
	},
	"addons": [
		{
			"name": "Collaboration",
			"package": "statamic\/collaboration",
			"version": "0.4.0",
			"latest_version": "0.4.0",
			"update_available": false
		},
		{
			"name": "Export",
			"package": "youfront\/statamic-export",
			"version": "1.0.1",
			"latest_version": "1.0.3",
			"update_available": true
		}
	],
	"updates_available": 2
}
```

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require marcorieser/statamic-vitals
```

## Configuration
Be sure to generate an access key which is used to authenticate external requests.
```bash
php please vitals:generate-key
```

## How to Use
Send a post request to the vitals endpoint `https://site.tld/api/statamic-vitals/vitals`.
```bash
curl --request POST \
  --url https://site.tld/api/statamic-vitals/vitals \
  --header 'Content-Type: application/json' \
  --data '{
	"access_key":"4Vz?xNc_eE&2uqQL"
}'
```

### Parameters
| Parameter     | Description                                    | Default | Required |
|---------------|------------------------------------------------|---------|:--------:|
| `access_key`  | The access key you put in your `.env` file     | `''`    |    *     |
| `clear_cache` | Whether the vitals should be generated freshly | `false` |          |
