### RamSources API

You will need to do a `composer install` to get requirements installed.  

The root of your project should include a config.php with the following array elements for DB connection. 
```
$dbconfig = array();
$dbconfig['db'] = '';
$dbconfig['host'] = '';
$dbconfig['un'] = "";
$dbconfig['pw'] = "";
```

#### We now have the current endpoints setup.  
`api.ramsources.com/v1/resource/all`                 *Returns all resources*
`api.ramsources.com/v1/resource/{id}`             *Returns a specific resource add* Note: does not need {} just put the id there.   
`api.ramsources.com/v1/resource/type/{type}`      *Returns all resources of a specific type*
`api.ramsources.com/v1/building/{bid}`            *Returns all resources in a building*
`api.ramsources.com/v1/building/all`                 *Returns all building on campus*

`api.ramsources.com/v1/user/new/{user}/{pass}/{name}`   *Creates a new user*
`api.ramsources.com/v1/user/new/login`      *logs user in*