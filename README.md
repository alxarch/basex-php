 BaseX client for PHP (>=5.3)
=============================

## Session

```php

use BaseX\Session;

// Open session

$session = new Session('localhost', 1984, 'username', 'password');

// Execute commands

$session->execute('INFO');

// Database creation

$session->execute('CREATE new_db');

$session->execute('OPEN new_db');

// Simple document add.

$session->add('file.xml', '<root><node/></root>');

// Adding via file handler

$fh = fopen('another_test.xml', 'r');

$session->add('another_test.xml', $fh);

// Get/Set options

$session->getOption('chop');
$session->getOption('parser');

$session->setOption('chop', true)
		->setOption('parser', 'json');

// Session information.

$session->getInfo()->dbpath;


// End session.

$session->close();

```

## Queries

```php

use BaseX\Session;
use BaseX\Query\Writer as QueryWriter;

$session = new Session('localhost', 1984, 'username', 'password');

$xql = "db:open('new_db', 'test.xml')";

$query = $session->query($xql);

$xml = $query->execute();

```

### QueryWriter

```php

$xql = "db:open(\$db, \$doc)"

$qw = QueryWriter::begin($xql)
	->setVariable('db', 'new_db')
	->setVariable('doc', 'another_test.xml');

$query = $qw->getQuery($session);

$result = $query->execute();


```

## Database Object

TODO

## Resource Object

TODO

## StreamWrapper

```php

use BaseX\Session;
use BaseX\StreamWrapper;

$session = new Session('localhost', 1984, 'username', 'password');

StreamWrapper::register($session);

$stream = fopen('basex://database/path/to/resource.xml', 'r');

$contents = stream_get_contents($stream);

fclose($stream);

$stream = fopen('basex://database/path/to/resource.xml', 'w');

$local = fopen('somefile.xml');

stream_copy_to_stream($local, $stream);

fclose($stream);
fclose($local);

```