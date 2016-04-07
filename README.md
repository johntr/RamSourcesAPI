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

GET `api.ramsources.com/v1/resource/all`                 *Returns all resources*  
GET `api.ramsources.com/v1/resource/id/{id}`             *Returns a specific resource add*    
GET `api.ramsources.com/v1/resource/type/{type}`      *Returns all resources of a specific type*  
GET `api.ramsources.com/v1/resource/detail/id/{id}`     *Returns detailed info about a resource*  
POST `api.ramsources.com/v1/resource/new`        *creates a new resource based on form values*  
PUT `api.ramsources.com/v1/resource/update/id/{id}`     *Updates resource info based on JSON data*   

GET `api.ramsources.com/v1/building/bid/{bid}`            *Returns all resources in a building*  
GET `api.ramsources.com/v1/building/all`                 *Returns all building on campus*    

POST `api.ramsources.com/v1/comment/new`         *Takes form values and adds a new comment*  
GET `api.ramsources.com/v1/comment/id/{id}`     *Returns comments based on resource id*  
DELETE `api.ramsources.com/v1/comment/delete/{id}` *Deletes comments on comment id*  

POST `api.ramsources.com/v1/rating/new`      *Creates a new rating based on form values*  
GET `api.ramsources.com/v1/rating/rid/{rid}`    *Returns rating info for a resource*  

POST `api.ramsources.com/v1/user/new/{user}/{pass}/{name}`   *Creates a new user*  
GET `api.ramsources.com/v1/user/new/login`      *logs user in*  
GET `api.ramsources.com/v1/user//userverify?id={hash}`      *Verifies users email address.*  
  
