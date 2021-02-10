<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Courses;
use App\Models\CourseItems;
use App\Models\Assignments;
use App\Traits\RolesTrait;

class CustomController extends Controller
{
    use RolesTrait;

    public function getBlock($id)
    {
        $courseItems = CourseItems::query()->with('course')->where('course_id', $id)->get();
        return view('custom.block',['courseItems' => $courseItems, 'id' => $id ] );
    }

    public function getCourses()
    {
        // $course = Courses::paginate(5);
        // return view('custom.courses', compact('course'));
        $assign = Assignments::with(['users', 'courses'])->paginate(5);

        return view('custom.mycourses', ['assignments' => $assign, 'role' => $this->getRole()]);
    }

    public function view($id)
    {
        // $assign = Assignments::with(['users', 'courses'])->where('users_id', $id)->get();
        // return view('custom.mycourses', ['assignments' => $assign->find($id)]);
    }
}