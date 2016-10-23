# Destinations #

## /destinations ##

### `GET` /destinations ###


#### Response ####

[]:

  * type: array of objects (Destination)

[][url]:

  * type: string

[][id]:

  * type: integer


### `POST` /destinations ###


#### Parameters ####

url:

  * type: string
  * required: true


### `DELETE` /destinations/{id} ###


#### Requirements ####

**id**



### `GET` /destinations/{id} ###


#### Requirements ####

**id**


#### Response ####

url:

  * type: string

id:

  * type: integer


### `PUT|PATCH` /destinations/{id} ###


#### Requirements ####

**id**


#### Parameters ####

url:

  * type: string
  * required: true

#### Response ####

url:

  * type: string

id:

  * type: integer



# Messages #

## /messages ##

### `GET` /messages ###


#### Response ####

[]:

  * type: array of objects (Message)

[][id]:

  * type: integer

[][destination]:

  * type: object (Destination)

[][destination][id]:

  * type: integer

[][destination][url]:

  * type: string

[][contentType]:

  * type: string

[][msgBody]:

  * type: string

[][createdAt]:

  * type: DateTime


### `POST` /messages ###


#### Parameters ####

destination:

  * type: integer
  * required: true
  * description: Destination ID.

contentType:

  * type: string
  * required: true

msgBody:

  * type: string
  * required: true


### `DELETE` /messages/{id} ###


#### Requirements ####

**id**



### `GET` /messages/{id} ###


#### Requirements ####

**id**


#### Response ####

id:

  * type: integer

destination:

  * type: object (Destination)

destination[id]:

  * type: integer

destination[url]:

  * type: string

contentType:

  * type: string

msgBody:

  * type: string

createdAt:

  * type: DateTime
