<?php

namespace Tests\Generators;

use Corp104\Eloquent\Generator\Generators\CommentGenerator;
use Tests\TestCase;

class CommentGeneratorTest extends TestCase
{
    /**
     * @var CommentGenerator
     */
    private $target;

    protected function setUp()
    {
        parent::setUp();

        $this->target = $this->createContainer()->make(CommentGenerator::class);
    }

    protected function tearDown()
    {
        $this->target = null;

        parent::tearDown();
    }

    /**
     * @test
     */
    public function shouldReturnCorrectContent()
    {
        $property = $this->createFieldsStub([
            'field_a' => 'integer',
            'field_b' => 'text',
            'field_c' => 'decimal',
        ]);

        $actual = $this->target->generate($property);

        $this->assertContains('int field_a', $actual);
        $this->assertContains('string field_b', $actual);
        $this->assertContains('float field_c', $actual);
    }

    /**
     * @test
     */
    public function shouldReturnEmptyStringWhenNoField()
    {
        $this->assertSame('', $this->target->generate([]));
    }
}