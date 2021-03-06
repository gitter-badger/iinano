<?php

/*
 * iinano
 */

namespace Trismegiste\SocialBundle\Tests\Unit\Form;

use Trismegiste\SocialBundle\Form\ProfileType;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

/**
 * ProfileTypeTest tests ProfileType
 */
class ProfileTypeTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Symfony\Component\Form\Form */
    protected $sut;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    protected $factory;

    protected function setUp()
    {

        $validator = Validation::createValidator();

        $this->factory = Forms::createFormFactoryBuilder()
                ->addExtension(new ValidatorExtension($validator))
                ->getFormFactory();

        $this->sut = $this->factory->create(new ProfileType());
    }

    public function testInvalid()
    {
        $this->sut->submit([
            'fullName' => '1234',
            'email' => 'gfdgfdfg@dgfgf'
        ]);
        $this->assertFalse($this->sut['fullName']->isValid());
        $this->assertFalse($this->sut['email']->isValid());

        $this->assertFalse($this->sut->isValid());
    }

    public function testValid()
    {
        $this->sut->submit([
            'fullName' => 'James T. Kirk',
            'dateOfBirth' => ['year' => 2004, 'month' => 11, 'day' => 13],
            'email' => 'jim@ufp.org'
        ]);

        foreach ($this->sut as $name => $child) {
            $this->assertTrue($child->isValid(), $name);
        }

        $this->assertTrue($this->sut->isValid());
    }

}
