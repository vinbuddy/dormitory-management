<?php

namespace App\Http\Controllers\Admin\Student;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Student;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function __construct()
    {
    }

    public function config()
    {
        return $config = [
            'js' => [
                'js/plugins/dataTables/datatables.min.js',
                'js/plugins/pace/pace.min.js',
                'js/plugins/footable/footable.all.min.js',

            ],
            'linkjs' => [
                'https://cdn.tailwindcss.com'
            ],
            'css' => [
                'css/plugins/dataTables/datatables.min.css',
                'css/plugins/footable/footable.core.css',
            ],
            'linkcss' => [],

            'script' => [
                '
                tailwind.config = {
                    prefix: \'tw-\',
                    corePlugins: {
                        preflight: false, // Set preflight to false to disable default styles
                    },
                }',
                '
                $(document).ready(function(){
                    $(\'.dataTables-example\').DataTable({
                        pageLength: 10,
                        lengthChange: true,
                        responsive: true,
                        info: false,
                        paging: true,
                        dom: \'<"html5buttons"B>lTfgitp\',
                        buttons: [
        
                            {extend: \'print\',
                             customize: function (win){
                                    $(win.document.body).addClass(\'white-bg\');
                                    $(win.document.body).css(\'font-size\', \'10px\');
        
                                    $(win.document.body).find(\'table\')
                                            .addClass(\'compact\')
                                            .css(\'font-size\', \'inherit\');
                            }
                            }
                        ]
        
                    });
        
                });
                ',

            ]
        ];
    }

    public function tailwindConfig()
    {
        return $config = [
            'js' => [],
            'linkjs' => [
                'https://cdn.tailwindcss.com'
            ],
            'css' => [],
            'linkcss' => [],

            'script' => [
                '
                tailwind.config = {
                    prefix: \'tw-\',
                    corePlugins: {
                        preflight: false, // Set preflight to false to disable default styles
                    },
                }',

            ]
        ];
    }

    public function index()
    {
        if (Auth::check()) {
            if (!Session::has('user')|| !Session::has('employee') || !Session::has('position_name')) {
                $authId = Auth::id();
                $user = User::find($authId);
                $employee = $user->employee;
                $employee_id = $employee->employee_id;
                $position_name = Position::find($employee_id)->position_name;
                Session::put('employee', $employee);
                Session::put('user', $user);
                Session::put('position_name', $position_name);
            }
            $employee = Session::get('employee');
            $position_name = Session::get('position_name');
            $user = Session::get('user');

            $students = Student::all();
            $data = ['students' => $students];
            $title = 'Student';


            $config = $this->config();
            $template = 'admin.student.index';

            // update contract status = 'expired' if end_date <= current date
            DB::table('contracts')
                ->where('end_date', '<=', date('Y-m-d'))
                ->where('status', '=', 'renting')
                ->update(['status' => 'expired']);

            return view('admin.dashboard.layout', compact(
                'template',
                'config',
                'data',
                'title',
                'employee',
                'position_name',
                'user'
            ));
        } else {
            return redirect()->route('auth.admin')->with('error', 'Please log in first');
        }
    }

    public function createView()
    {
        if (Auth::check()) {
            if (!Session::has('user')|| !Session::has('employee') || !Session::has('position_name')) {
                $authId = Auth::id();
                $user = User::find($authId);
                $employee = $user->employee;
                $employee_id = $employee->employee_id;
                $position_name = Position::find($employee_id)->position_name;
                Session::put('employee', $employee);
                Session::put('user', $user);
                Session::put('position_name', $position_name);
            }
            $employee = Session::get('employee');
            $position_name = Session::get('position_name');
            $user = Session::get('user');


            $title = 'Create student';

            $config = $this->tailwindConfig();

            $template = 'admin.student.create';

            return view('admin.dashboard.layout', compact(
                'template',
                'config',
                'title',
                'employee',
                'position_name',
                'user'
            ));
        } else {
            return redirect()->route('auth.admin')->with('error', 'Please log in first');
        }
    }

    public function create(Request $request)
    {
        $request->validate([
            'student_id' => 'required|unique:students',
            'name' => 'required',
            'class' => 'required',
            'date_of_birth' => 'required|date',
            'gender' => 'required',
            'person_id' => 'required|unique:students',
            'avatar' => 'required|image|mimes:jpg,png,jpeg',
            'phone_number' => 'required|unique:students',
        ]);


        $image = $request->file('avatar');
        $imageName = time() . '.' . $image->getClientOriginalName();
        $path = public_path("/uploads/avatars");
        $image->move($path, $imageName);

        $avatar = $imageName;

        $student = new Student();
        $student->student_id = $request->student_id;
        $student->name = $request->name;
        $student->date_of_birth = $request->date_of_birth;
        $student->gender = $request->gender;
        $student->class = $request->class;
        $student->person_id = $request->person_id;
        $student->phone_number = $request->phone_number;
        $student->avatar = $avatar;

        $result = $student->save();

        if ($result) {
            return redirect()->route('student.index')->with('success', 'Student created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create student.');
        }
    }

    public function editView($id)
    {
        if (Auth::check()) {
            if (!Session::has('user')|| !Session::has('employee') || !Session::has('position_name')) {
                $authId = Auth::id();
                $user = User::find($authId);
                $employee = $user->employee;
                $employee_id = $employee->employee_id;
                $position_name = Position::find($employee_id)->position_name;
                Session::put('employee', $employee);
                Session::put('user', $user);
                Session::put('position_name', $position_name);
            }
            $employee = Session::get('employee');
            $position_name = Session::get('position_name');
            $user = Session::get('user');


            // Config - template
            $title = 'Edit student';

            $config = $this->tailwindConfig();
            $template = 'admin.student.edit';


            // Data 
            $student = Student::find($id);

            return view('admin.dashboard.layout', compact(
                'template',
                'config',
                'title',
                'employee',
                'position_name',
                'student',
                'user'
            ));
        } else {
            return redirect()->route('auth.admin')->with('error', 'Please log in first');
        }
    }

    public function edit(Request $request, $id)
    {
        $student = Student::find($id);

        $request->validate([
            'name' => 'required',
            'class' => 'required',
            'date_of_birth' => 'required|date',
            'gender' => 'required',
            'person_id' => [
                'required',
                Rule::unique('students', 'person_id')->ignore($student->person_id, 'person_id'),
            ],
            'phone_number' => [
                'required',
                Rule::unique('students', 'phone_number')->ignore($student->phone_number, 'phone_number'),
            ],
        ]);


        // $values = $request->except('avatar');

        if ($request->hasFile('avatar')) {
            $oldAvatarPath = public_path("/uploads/avatars/" . $student->avatar);
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
            // Upload the new avatar
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalName();
            $path = public_path("/uploads/avatars");
            $image->move($path, $imageName);

            $student->name = $request->name;
            $student->date_of_birth = $request->date_of_birth;
            $student->gender = $request->gender;
            $student->class = $request->class;
            $student->person_id = $request->person_id;
            $student->phone_number = $request->phone_number;
            $student->avatar = $imageName;
        } else {
            $student->name = $request->name;
            $student->date_of_birth = $request->date_of_birth;
            $student->gender = $request->gender;
            $student->class = $request->class;
            $student->person_id = $request->person_id;
            $student->phone_number = $request->phone_number;
        }




        $result = $student->save();

        if ($result) {
            return redirect()->route('student.index')->with('success', 'Student edited successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update student.');
        }
    }

    public function detailView($id)
    {
        if (Auth::check()) {
            if (!Session::has('user')|| !Session::has('employee') || !Session::has('position_name')) {
                $authId = Auth::id();
                $user = User::find($authId);
                $employee = $user->employee;
                $employee_id = $employee->employee_id;
                $position_name = Position::find($employee_id)->position_name;
                Session::put('employee', $employee);
                Session::put('user', $user);
                Session::put('position_name', $position_name);
            }
            $employee = Session::get('employee');
            $position_name = Session::get('position_name');
            $user = Session::get('user');


            // update contract status = 'expired' if end_date <= current date
            DB::table('contracts')
                ->where('end_date', '<=', date('Y-m-d'))
                ->where('status', '=', 'renting')
                ->update(['status' => 'expired']);

            // Config - template
            $title = 'Student detail';

            $config = $this->config();
            $template = 'admin.student.detail';


            // Data 
            $student = Student::find($id);

            // Check if student has any contract and status = 'renting'
            $isAvailableCreateContract = true;
            $contract = Contract::where('student_id', $id)->where('status', 'renting')->first();
            if ($contract) {
                $isAvailableCreateContract = false;
            }

            $contracts = Contract::where('student_id', $id)->get();


            return view('admin.dashboard.layout', compact(
                'template',
                'config',
                'title',
                'employee',
                'position_name',
                'student',
                'contracts',
                'isAvailableCreateContract',
                'user'
            ));
        } else {
            return redirect()->route('auth.admin')->with('error', 'Please log in first');
        }
    }
}
