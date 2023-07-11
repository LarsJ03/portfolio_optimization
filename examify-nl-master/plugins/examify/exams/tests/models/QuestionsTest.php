<?php namespace Examify\Exams\Tests\Models;

use Examify\Exams\Models\Questions as Questions;
use PluginTestCase;
use October\Rain\Exception\ValidationException as ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuestionsTest extends PluginTestCase
{

	use DatabaseTransactions;

	public function testCreateFirstQuestionWithoutQuestion()
	{
		$question = new Questions();
		$question->text_id = 1;
		$question->question_nr = 1;
		$this->expectException(ValidationException::class);
		$question->save();
	}

	public function testCreateFirstQuestion()
	{
		$question = new Questions();
		$question->text_id = 1;
		$question->question_nr = 1;
		$question->type_id = 1;
		$question->question_builder = [
			[
				'format' => 'name',
				'name' => 'Which of the following fits the gap?'	
			],
			[
				'format' => 'textarea',
				'textarea' => "\"conflicts between scientific discovery and religious beliefs\" (paragraph 11)"
			]
		];
		$question->points = 3;
		$question->answer_type = 'multiplechoice_single';
		$question->answers_content = [
			[
				'name' => 'ethical principles',
				'points' => 0
			],
			[
				'name' => 'In her letter Jillian informs Natalie why',
				'points' => 1
			],
			[
				'name' => 'To address the issue',
				'points' => 1
			]
		];

		$question->save();
		//$this->assertEquals(1, $question->id);

		// validate that answers are found
		$answers = $question->answers();
		$this->assertEquals(3, $answers->count(), 'The number of answers coupled to this question are not as expected.');

		$expected = [
			'ethical principles',
			'In her letter Jillian informs Natalie why',
			'To address the issue'
		];
		$get = $answers->pluck('name')->values()->all();
		$this->assertEquals($expected, $get, 'Answer names are not as expected.');

		$ids = $answers->pluck('id')->values()->all();
		$this->assertEquals(3, count($ids), 'The length of answer ids is not as expected.');

		foreach($ids as $id){
			$this->assertGreaterThan(0, $id, 'The id should be nonzero');
		}

	}

}

?>