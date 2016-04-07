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

POST `api.ramsources.com/v1/user/new`   *Creates a new user*  
GET `api.ramsources.com/v1/user/new/login`      *logs user in*  
GET `api.ramsources.com/v1/user/userverify?id={hash}`      *Verifies users email address.*  

### Adding Content
#### Resources
To put a new resource you will need to use the following structure as an example. You will need all of the resource keys as well as the resourcetype_dbcolumn for that resource.
```
resource_resource_type:bathroom
resource_floor:2
resource_building_id:2
resource_resource_name:building-2-1
bathroom_num_stalls:4
bathroom_sex:m
bathroom_soap_type:liquid
bathroom_dryer_type:blower
bathroom_num_urinals:3
```

To update a resource you will need to pass the following structre in the body of the put request. 
```
{
"resource_data" : {
        "resource_id" : "1",
        "building_id" : "21",
        "resource_type" : "vending",
        "resource_name" : "vedning_1_21",
        "floor" : "1"
    },
"type_data" : {
        "vending_id" : "1",
        "pay_type" : "cash",
        "type" : "coffee"
    }
}
```  
#### Comments
To add a new comment you will need to pass the following **form** data in the POst request. 
```
 "parent_comment":"0",
 "comment":"This is a test comment. Here we are putting some comment data.",
 "user_id":"2",
 "resource_id":"1"
```
#### Ratings
To add a new rating. You will need the folowing form data in a POST request. 
```
rating:3
resource_id:1
```
#### Users
To add a new user you will need the following form data. 
```
user:example@farmingdale.edu
pass:test
name:test-student
```