# From HootSuite # 

As more and more integration takes place between SaaS providers, other SaaS providers and their customers, webhooks have become an invaluable way of sharing events.  These events simplify data-synchronization and extensibility.  

**Project:** 
Write a webhook calling service that will reliably POST data to destination URLs in the order POST message requests are received. 

The service should support the following remote requests via REST 
register a new destination (URL) returning its id 
list registered destinations [{id, URL},...] 
delete a destination by id 
POST a message to this destination (id, msg-body, content-type): this causes the server to POST the given msg-body to the URL associated with that id.  

**Behaviour:** 
If the destination URL is not responding (e.g. the servier is down) or returns a non-200 response, your service should resend the message at a later time 
Messages not sent within 24 hours can be be deleted 
Messages that failed to send should retried 3 or more times before they are deleted 
Message ordering to a destination should be preserved, even when there are pending message retries for that destination 
Feel free to add more metadata to the destination (id, URL,) if it helps your implementation 

**To Consider:** 
is your API using the standard REST-ful conventions for the 4 operations? 
how can I scale out this service across multiple servers while preserving per-destination ordering? 
how well does your service support concurrency for multiple destinations while preserving per-destination ordering? 
how secure is this? should you require HTTPS urls? should the content be signed with something like an HMAC?  Should any url be allowed (e.g. one that has or resolves to a private IP address?)