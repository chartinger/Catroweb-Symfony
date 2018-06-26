Feature: Authenticate to the system
  How to register and login to the system

  Background:
   
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |


  Scenario: Registration of a new user
    Given the next generated token will be "rrrrrrrrrrr"
     When I POST the following parameters to "/pocketcode/api/loginOrRegister/loginOrRegister.json":
          | Name                 | Value                |
          | registrationUsername | newuser              |
          | registrationPassword | registrationpassword |
          | registrationEmail    | test@mail.com        |
          | registrationCountry  | at                   |
     Then the returned json object will be:
          """
          {
            "token": "rrrrrrrrrrr",
            "statusCode": 201,
            "answer": "Registration successful!",
            "preHeaderMessages": ""
          }
          """

  Scenario Outline: Troubleshooting
     When there is a "<problem>" with the registration request
     Then the returned json object will be:
          """
          {
            "statusCode": "<errorcode>",
            "answer": "<answer>",
            "preHeaderMessages": ""
          }
          """
        
          Examples:
          | problem           | errorcode | answer                   |
          | no password given | 602       | The password is missing. |
        
  Scenario: Retrieve the upload token of a user
      When I POST the following parameters to "/pocketcode/api/loginOrRegister/loginOrRegister.json":
          | name                 | value                 |
          | registrationUsername | Catrobat              |
          | registrationPassword | 12345                 |
      Then the returned json object will be:
          """
          {
            "token": "cccccccccc",
            "statusCode": 200,
            "preHeaderMessages": ""
          }
          """

  Scenario: Checking a given token for its validity
    When I POST the following parameters to "/pocketcode/api/checkToken/check.json":
          | Name     | Value      |
          | username | Catrobat   |
          | token    | cccccccccc |
     Then the returned json object will be:
          """
          {
            "statusCode": 200,
            "answer": "ok",
            "preHeaderMessages": "  \n"
          }
          """
      And the response code will be "200"

  Scenario Outline: Troubleshooting
     When there is a "<problem>" with the check token request
     Then the returned json object will be:
          """
          {
            "statusCode": "<errorcode>",
            "answer": "<answer>",
            "preHeaderMessages": ""
          }
          """
      And the response code will be "<httpcode>"
        
          Examples:
          | problem            | errorcode | answer                                               | httpcode  |
          | invalid token      | 601       | Authentication of device failed: invalid auth-token! | 401       |

