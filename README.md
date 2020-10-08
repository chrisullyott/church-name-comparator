# church-name-comparator

Compare two church names to see if they refer to the same congregation.

### Instantiate

```php
$comparator = new ChrisUllyott\ChurchNameComparator();
```

### Compare

```php
$string1 = "First Baptist Church of South Lake";
$string2 = "fbc south lake";

$comparator->setStrings($string1, $string2);
$result = $comparator->isMatch();  
// bool(true)
```

```php
$string1 = "St. Bartholomew's Church";
$string2 = "St Bart's";

$comparator->setStrings($string1, $string2);
$result = $comparator->isMatch();  
// bool(true)
```

```php
$string1 = "Grace Episcopal Church";
$string2 = "Grace SBC";

$comparator->setStrings($string1, $string2);
$result = $comparator->isMatch();  
// bool(false)
```
