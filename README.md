# Statamic Vitals
Provide vitals of you Statamic installation for external usage.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require marcorieser/statamic-vitals
```

## Configuration
Be sure to add the access key to your `.env` file which is used to authenticate external requests.
```dotenv
STATAMIC_VITALS_ACCESS_KEY="4Vz?xNc_eE&2uqQL"
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
