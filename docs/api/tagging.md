# Get a list of allowed tags
> How to get a list of all supported tags for a program

## Get the available english tags
> 

When I GET "`/pocketcode/api/tags/getTags.json`" with parameters:

| Name | Value |
| --- | --- |
| language | en |
   
Then the returned json object will be:
```json
{
  "statusCode": 200,
  "constantTags":[
                    "Games",
                    "Story",
                    "Music",
                    "Art"
                 ]
}
```
 
 


---

## No language parameters
> 

When the tags are requested without a language parameter
 
Then the "`statusCode`" in the error JSON will be 404
 
And the "`constantTags`" array will show the english tags.
 
 


---

## Troubleshooting
> 

When there is a "`<problem>`" with the tag request
 
Then the returned json object will be:
```json
{
  "statusCode": <errorcode>,
  "constantTags": <returnedTags>
}
```
 
 

### Examples

| problem | errorcode | returnedTags |
| --- | --- | --- |
| no language given | 404 | [&quot;Games&quot;, &quot;Story&quot;, &quot;Music&quot;, &quot;Art&quot;] |
| unsupported language | 404 | [&quot;Games&quot;, &quot;Story&quot;, &quot;Music&quot;, &quot;Art&quot;] |

---

  
# Background

And there are tags:

| id | en | de |
| --- | --- | --- |
| 1 | Games | Spiele |
| 2 | Story | Geschichte |
| 3 | Music | Musik |
| 4 | Art | Kunst |
   
 