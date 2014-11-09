@block @block_mooprofile
Feature: I can display profiles by role
  In order to display profiles by role in course
  As an teacher
  I need to create block and select role

  @javascript
  Scenario: Display profile block for teachers in a course
    Given the following "courses" exist:
        | fullname | shortname | category |
        | Course 1 | C1 | 0 |
      And the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | First | teacher1@asd.com |
        | student1 | Student | First | student1@asd.com |
     And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | student1 | C1 | student |
     And I log in as "teacher1"
     And I follow "Course 1"
     And I turn editing mode on
     And I add the "MooProfile Block" block
     And I open the "MooProfile Block" blocks action menu
     And I follow "Configure MooProfile Block block"
     And I set the following fields to these values:
       | Block Title     | Course Teacher |
       | or Role         | 3              |
       | Display name    | Yes            |
       | Display picture | Yes            |
     And I press "Save changes"
     And I log out
    When I log in as "student1"
     And I follow "Course 1"
    Then I should see "Course Teacher" in the "block_mooprofile" "block"
     And I should see "Teacher First" in the "block_mooprofile" "block"
     And I should not see "teacher1@asd.com" in the "block_mooprofile" "block"
