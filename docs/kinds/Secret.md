# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/configuration/secret/)

## Example

### Secret creation

Passing `->setData()` without a second parameter will automatically encode each field for you:

```php
$secret = K8s::secret($cluster)
    ->setName('certificates')
    ->setData([
        'key.pem' => '...',
        'ca.pem' => '...',
    ])
    ->create();
```

If you already encoded the fields, you can pass `false` to the second parameter:

```php
$secret->setData([
    'crt.pem' => base64_encode('...'),
], false)
```

### Data Retrieval

Data retrieval by default returns base64-encoded data:

```php
$secret = K8s::secret($cluster)
    ->whereName('certificates')
    ->get();

$data = $secret->getData();

$key = $data['key.pem']; // this string is base64 encoded
```

Passing `true` to the `getData()` method will decode the data for you:

```php
$data = $secret->getData(true);

$key = $data['key.pem'] // '...'
```

### Removing an attribute from data

```php
$secret = K8s::secret($cluster)
    ->whereName('certificates')
    ->get();

// ['key.pem' => '...', 'ca.pem' => '...']

$secret
    ->removeData('ca.pem')
    ->replace();

$data = $secret->getData(); // ['key.pem' => '...']
```
