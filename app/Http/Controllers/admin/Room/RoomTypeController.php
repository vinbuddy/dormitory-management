<?php

namespace App\Http\Controllers\Admin\Room;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    public function __construct()
    {
    }

    public function config()
    {
        return $config = [
            'js' => [
                'js/plugins/flot/jquery.flot.js',
                'js/plugins/flot/jquery.flot.js',
                'js/plugins/flot/jquery.flot.tooltip.min.js',
                'js/plugins/flot/jquery.flot.spline.js',
                'js/plugins/flot/query.flot.resize.js',
                'js/plugins/flot/query.flot.resize.js',
                'js/plugins/flot/jquery.flot.pie.js',
                'js/plugins/peity/jquery.peity.min.js',
                'js/demo/peity-demo.js',
                'js/inspinia.js',
                'js/plugins/gritter/jquery.gritter.min.js',
                'js/demo/sparkline-demo.js',
                'js/plugins/sparkline/jquery.sparkline.min.js',
                'js/plugins/chartJs/Chart.min.js',
                'js/plugins/toastr/toastr.min.js',

            ],
            'css' => [
                'css/dashboard.css'
            ]
        ];
    }

    public function index()
    {
        $roomTypes = RoomType::all();
        $data = ['roomTypes' => $roomTypes];
        $id = Auth::id();
        $title = 'Room type list';

        $employee = Employee::find($id);
        $employee_id = $employee->employee_id;
        $position_name = Position::find($employee_id)->position_name;

        $config = $this->config();
        $template = 'admin.room.type.index';

        return view('admin.dashboard.layout', compact(
            'template',
            'config',
            'data',
            'title',
            'employee',
            'position_name'
        ));
    }

    public function createView()
    {
        $id = Auth::id();
        $title = 'Create room type';
        $employee = Employee::find($id);
        $employee_id = $employee->employee_id;
        $position_name = Position::find($employee_id)->position_name;
        $config = $this->config();

        $template = 'admin.room.type.create';


        return view('admin.dashboard.layout', compact(
            'template',
            'config',
            'title',
            'employee',
            'position_name'
        ));
    }

    public function create(Request $request)
    {
        $request->validate([
            'room_type_name' => 'required|unique:room_types',
            'room_type_price' => 'required|numeric',
        ]);

        $roomType = new RoomType();
        $roomType->room_type_name = $request->room_type_name;
        $roomType->room_type_price = $request->room_type_price;
        $roomType->save();

        return redirect()->route('roomType.index');
    }

    public function editView($id)
    {
        $authId = Auth::id();
        $title = 'Edit room type';
        $employee = Employee::find($authId);
        $employee_id = $employee->employee_id;
        $position_name = Position::find($employee_id)->position_name;
        $config = $this->config();

        $template = 'admin.room.type.edit';

        $roomType = RoomType::find($id);

        return view('admin.dashboard.layout', compact(
            'template',
            'config',
            'title',
            'employee',
            'position_name',
            'roomType'
        ));
    }

    public function edit(Request $request, $id)
    {
        $roomType = RoomType::find($id);

        // Validate
        $request->validate([
            'room_type_name' => [
                'required',
                Rule::unique('room_types', 'room_type_name')->ignore($roomType->room_type_name, 'room_type_name'),
            ],
            'room_type_price' => 'required|numeric',
        ]);

        // Update
        $roomType->room_type_name = $request->room_type_name;
        $roomType->room_type_price = $request->room_type_price;
        $result = $roomType->save();

        return redirect()->route('roomType.index');
    }
}