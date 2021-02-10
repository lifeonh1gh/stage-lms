<?php


namespace App\Http\Controllers;

use App\Http\Requests\CreateTestRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Test;
use App\Traits\RolesTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class TestsController
{
    use RolesTrait;


    public function testIndex(int $id)
    {
        $courseItemTest = Test::query()->with('courses')->where('course_id', $id)->get();
        return view('admin/allTests', ['courseItemTest' => $courseItemTest, 'id' => $id, 'role' => $this->getRole()]);
    }

    public function show($id)
    {

        $test = Test::with(['questions.answers'])->find($id);

        return view('admin/showTest', ['test' => $test, 'role' => $this->getRole()]);
    }

    public function testEdit($id)
    {
        $test = Test::with(['questions.answers'])->find($id);
        return view('admin/testEdit', ['test' => $test, 'role' => $this->getRole() ]);
    }

    public function testUpdate(Request $request, $id)
    {
        /**
         * @var UploadedFile $image
         */
        $test = Test::with(['questions.answers'])->find($id);
        $request->validate([
            'name' => 'required',
            'image'  =>  'image|max:2048'
        ]);
        $test::find($id)->update(['name' => $request->get('name')]);

        $questions = $test->questions;
        foreach ($questions as $question) {
            $questionId = $question['id'];
            $request->validate([
                'questions-' . $questionId => 'required',
            ]);
            Question::find($questionId)->update(['question' => $request->get('questions-' . $questionId)]);
            $answers = $question->answers;
            foreach ($answers as $answer) {

                $answerId = $answer['id'];
                $request->validate([
                    'answers-' . $answerId => 'required',
                ]);

                $isCorrect = $request->get('is_correct-' . $answerId);
                if ($isCorrect === 1 or $isCorrect === 'on') {
                    $isCorrect1 = 1;
                } else {
                    $isCorrect1 = 0;
                }
                Answer::find($answerId)->update(['answer' => $request->get('answers-' . $answerId),
                    'is_correct' => $isCorrect1 ]);
            }
        }

        return redirect(Route('admin.courses-testIndex', $test->course_id))->with('success', 'Тест обновлён!');
    }

    public function destroy($id)
    {

        $question = new Question();
        $question_id = $question::query()->with('answers')->where('test_id', $id)->get('id');

        foreach ($question_id as $item) {
            $questionFind = $question::findOrFail($item->id);
            $questionFind->answers()->delete();
            $questionFind->delete();
        }

        $test = Test::find($id);

        $test->delete();

        return redirect(Route('admin.courses-testIndex', $test->course_id))->with('success', 'Тест удалён');
    }

    public function testCreate(int $id)
    {
        $courseItemTest = Test::query()->with('courses')->where('course_id', $id)->get();
        return view('admin/test-block', ['courseItemTest' => $courseItemTest, 'id' => $id, 'role' => $this->getRole()]);
    }

    public function testStore(CreateTestRequest $request, int $id)
    {

        $validated = $request->validated();

        $questionsData = $request->get('questions');
        DB::beginTransaction();

        try {
            $test = new Test([
                'name' => $request->get('name'),
                'course_id' => $id,
            ]);
            $test->save();

            foreach ($questionsData as $questionItem) {
                if (!isset($questionItem['name'])) {
                    //
                    continue;
                }
                $answersData = $questionItem['answers'];

                /**
                 * @var Question $question
                 */
                /**
                 * @var UploadedFile $image
                 */
                if ($image = $request->file('image')) {
                    $path = Storage::put('', $image);
                    $question = $test
                        ->questions()
                        ->create(
                            [
                                'question' => $questionItem['name'],
                                'image'   =>   $path
                            ]
                        );
                } else {
                    $question = $test
                        ->questions()
                        ->create(
                            [
                                'question' => $questionItem['name'],
                            ]
                        );
                }

                $preparedAnswerData = array_map(function ($value) {
                    $isCorrect = isset($value['is_correct']) && $value['is_correct'] === 'on';

                    return ['answer' => $value['answer'], 'is_correct' => $isCorrect];
                }, $answersData);

                if (count($preparedAnswerData)) {
                    //                    $request->validate([
                    //                        'answer' => 'required|string|max:255',
                    //                    ]);

                    $question->answers()->createMany($preparedAnswerData);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect(Route('admin.courses-testIndex', $id));
    }
}
