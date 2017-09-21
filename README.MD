path definitions
use named subpatterns for path regex definitions

'^projects/(?P<_id>[^/]*)/?$'

The above will match the current path of projects/2657-dbd-121

and return the following
$parameters = array(
    '_id' = '2657-dbd-121'
)


```
Regex quick reference
[abc]     A single character: a, b or c
[^abc]     Any single character but a, b, or c
[a-z]     Any single character in the range a-z
[a-zA-Z]     Any single character in the range a-z or A-Z
^     Start of line
$     End of line
\A     Start of string
\z     End of string
.     Any single character
\s     Any whitespace character
\S     Any non-whitespace character
\d     Any digit
\D     Any non-digit
\w     Any word character (letter, number, underscore)
\W     Any non-word character
\b     Any word boundary character
(...)     Capture everything enclosed
(a|b)     a or b
a?     Zero or one of a
a*     Zero or more of a
a+     One or more of a
a{3}     Exactly 3 of a
a{3,}     3 or more of a
a{3,6}     Between 3 and 6 of a
```

```

class Base{
    function __construct(){
        echo "Base Class";
    }
    function test(){
        echo "test method";
    }
}
class test {

    function __construct(Base $base){

    }
}

$class = new ReflectionClass('test');
$con = $class->getConstructor()->getParameters();;
foreach($con as $parm){
    $cl = $parm->getClass()->name;
    var_dump($cl);
    $h = (new $cl())->test();
}
die();

```

use func_get_args() to get all args passed to your method/function
by default SimpleRESTAPI will pass url_tokens, Post/Get/File arguments, API Object Reference
