# Namespace

- [Official Documentation](https://kubernetes.io/docs/concepts/configuration/configmap/)

## Example

### Config Map creation

```php
$cm = K8s::configmap($cluster)
    ->setName('certificates')
    ->setData([
        'key.pem' => '...',
        'ca.pem' => '...',
    ])
    ->create();
```

```php
$
```

### Data Retrieval

```php
$cm = K8s::configmap($cluster)
    ->whereName('certificates')
    ->get();

$data = $cm->getData();

$key = $data['key.pem'];
```

### Removing an attribute from data

```php
$cm = K8s::configmap($cluster)
    ->whereName('certificates')
    ->get();

// ['key.pem' => '...', 'ca.pem' => '...']

$cm
    ->removeData('ca.pem')
    ->replace();

$data = $cm->getData(); // ['key.pem' => '...']
```
