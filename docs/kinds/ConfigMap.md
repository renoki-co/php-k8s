# Config Map

- [Official Documentation](https://kubernetes.io/docs/concepts/configuration/configmap/)

## Example

### Config Map creation

```php
$cm = $cluster->configmap()
    ->setName('certificates')
    ->setData([
        'key.pem' => '...',
        'ca.pem' => '...',
    ])->create();
```

### Data Retrieval

```php
$cm = $cluster->getConfigmapByName('certificates');

$data = $cm->getData();

$key = $data['key.pem'];
```

### Removing an attribute from data

```php
$cm = $cluster->getConfigmapByName('certificates');

// ['key.pem' => '...', 'ca.pem' => '...']

$cm->removeData('ca.pem')->update();

$data = $cm->getData(); // ['key.pem' => '...']
```
