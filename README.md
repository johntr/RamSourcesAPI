### RamSources API

You will need to do a `composer install` to get requirements installed.
Run `composer dumpautoload` to update composer namespace after adding new classes.   

The root of your project should include a config.php with the following array elements for DB connection.
```
$dbconfig = array();
$dbconfig['db'] = '';
$dbconfig['host'] = '';
$dbconfig['un'] = "";
$dbconfig['pw'] = "";

$dbconfig['token'] = "";
```

#### We now have the current endpoints setup.  
*Note: does not need {} just put the id there.*    

`api.ramsources.com/v1/resource/all`                 *Returns all resources*  
`api.ramsources.com/v1/resource/id/{id}`             *Returns a specific resource add*    
`api.ramsources.com/v1/resource/type/{type}`      *Returns all resources of a specific type*  
`api.ramsources.com/v1/resource/detail/id/{id}`     *Returns detailed info about a resource*  
`api.ramsources.com/v1/resource/new`        *creates a new resource based on form values*  
`api.ramsources.com/v1/resource/update/id/{id}`     *Updates resource info based on JSON data*   

`api.ramsources.com/v1/building/bid/{bid}`            *Returns all resources in a building*  
`api.ramsources.com/v1/building/all`                 *Returns all building on campus*    

`api.ramsources.com/v1/comment/new`         *Takes form values and adds a new comment*  
`api.ramsources.com/v1/comment/id/{id}`     *Returns comments based on resource id*  
`api.ramsources.com/v1/comment/delete/{id}` *Delets comments on comment id*  

`api.ramsources.com/v1/rating/new`      *Creates a new rating based on form values*  
`api.ramsources.com/v1/rating/rid/{rid}`    *Returns rating info for a resource*  

`api.ramsources.com/v1/user/new/{user}/{pass}/{name}`   *Creates a new user*  
`api.ramsources.com/v1/user/new/login`      *logs user in*  
