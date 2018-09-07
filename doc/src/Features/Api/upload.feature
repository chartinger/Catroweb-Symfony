Feature: Upload a program to the website

  Background:
   
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And we assume the next generated token will be "rrrrrrrrrrr"

  Scenario: Upload program
    Given a valid catrobat program with the MD5 checksum "682432b25a2968260c9a307a751a6e16"
    When I upload the file to "/pocketcode/api/upload/upload.json" with POST parameters:
          | Name          | Value                            |
          | username      | Catrobat                         |
          | token         | cccccccccc                       |
          | fileChecksum  | 682432b25a2968260c9a307a751a6e16 |
     Then the returned json object will be:
          """
          {
            "projectId": "1",
            "statusCode": 200,
            "answer": "Your project was uploaded successfully!",
            "token": "rrrrrrrrrrr",
            "preHeaderMessages": ""
          }
          """

  Scenario Outline: Troubleshooting
    Given the upload problem "<problem>"
     When such a Request is invoked
     Then the returned json object will be:
          """
          {
            "statusCode": "<errorcode>",
            "answer": "<answer>",
            "preHeaderMessages": ""
          }
          """
        
          Examples:
          | problem              | errorcode | answer                                               |
          | no authentication    | 601       | Authentication of device failed: invalid auth-token! |
          | missing parameters   | 501       | POST-Data not correct or missing!                    |
          | invalid program file | 505       | invalid file                                         | 
