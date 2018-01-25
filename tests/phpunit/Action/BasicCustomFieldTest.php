<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Contact;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;

/**
 * @group headless
 */
class BasicCustomFieldTest extends BaseCustomValueTest {

  public function testWithSingleField() {

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('extends', 'Contact')
      ->execute()
      ->getArrayCopy();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('custom_group_id', $customGroup['id'])
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Johann')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('MyContactFields.FavColor', 'Red')
      ->execute()
      ->getArrayCopy()['id'];

    $contact = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('MyContactFields.FavColor')
      ->addWhere('id', '=', $contactId)
      ->addWhere('MyContactFields.FavColor', '=', 'Red')
      ->execute()
      ->first();

    $this->assertArrayHasKey('MyContactFields', $contact);
    $contactFields = $contact['MyContactFields'];
    $this->assertArrayHasKey('FavColor', $contactFields);
    $this->assertEquals('Red', $contactFields['FavColor']);

    Contact::update()
      ->addWhere('id', '=', $contactId)
      ->setValue('MyContactFields.FavColor', 'Blue')
      ->execute();


    $contact = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('MyContactFields.FavColor')
      ->addWhere('id', '=', $contactId)
      ->execute()
      ->first();

    $contactFields = $contact['MyContactFields'];
    $this->assertEquals('Blue', $contactFields['FavColor']);
  }

}
