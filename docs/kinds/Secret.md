# Secret

- [Official Documentation](https://kubernetes.io/docs/concepts/configuration/secret/)

## Example

```php
$secret = $cluster->secret()
    ->setName('certificates')
    ->setData([
        'key.pem' => '...',
        'ca.pem' => '...',
    ])->create();
```

If you already encoded the fields, you can pass `false` to the second parameter:

```php
$secret->setData([
    'crt.pem' => base64_encode('...'),
], false)
```

## Data Retrieval

Data retrieval by default returns base64-encoded data:

```php
$secret = $cluster->getSecretByName('certificates');

$data = $secret->getData();

$key = $data['key.pem']; // this string is base64 encoded
```

Passing `true` to the `getData()` method will decode the data for you:

```php
$data = $secret->getData(true);

$key = $data['key.pem'] // '...'
```

## Immutability

Since Kubernetes v1.21.0, Secrets support immutability. If you do not specify the `immutable()` method, it will default to false:

```php
$secret = $cluster->secret()
    ...
    ->immutable()
    ->create();

$secret->isImmutable(); // true
```
