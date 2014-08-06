#PHP Rest Framework

### Available RESTful methods

* GET - fetching
* POST - creating
* PUT - replacing
* DELETE - deleting

### Callback examples

HTTP_METHOD /request_class/params --> request_class::HTTP_METHOD(params)

GET /users --> Users::get() // Retrieve all users
POST /users --> Users::POST() // Add new user
GET /users/1 --> Users::get(1) // Get the user with ID 1
DELETE /users --> Users::delete() // Delete all users
DELETE /users/100 --> Users::delete(100) // Delete the user with ID 100

GET /users/50/test --> Users::get(50, 'test')
GET /users/50/test/test2 --> Users::get(50, 'test', 'test2')
