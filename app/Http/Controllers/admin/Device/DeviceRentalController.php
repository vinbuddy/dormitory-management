<?php
namespace App\Http\Controllers\Admin\Device;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\DeviceRental;
use App\Models\DeviceRentalDetail;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;

class DeviceRentalController extends Controller
{
    public function __construct()
    {
    }

    public function config()
    {
        return $config = [
            'js' => [
                'js/device.js',
                'js/devicerental.js',
                'js/plugins/dataTables/datatables.min.js',
                'js/plugins/pace/pace.min.js',
                'js/plugins/footable/footable.all.min.js',
            ],
            'linkjs' => [],
            'css' => [
                'css/device.css',
                'css/plugins/dataTables/datatables.min.css',
                'css/plugins/footable/footable.core.css',
            ],
            'linkcss' => [],
            'script' => [
                '
                $(document).ready(function(){
                    var table = $(\'.dataTables-example\').DataTable({
                        pageLength: 10,
                        lengthChange: false,
                        responsive: true,
                        info: false,
                        ordering: true,
                        paging: true,
                        dom: \'<"html5buttons"B>lTfgitp\',
                        buttons: [
                            {
                                extend: \'print\',
                                customize: function (win){
                                    $(win.document.body).addClass(\'white-bg\');
                                    $(win.document.body).css(\'font-size\', \'10px\');
            
                                    $(win.document.body).find(\'table\')
                                        .addClass(\'compact\')
                                        .css(\'font-size\', \'inherit\');
                                },
                                exportOptions: {
                                    columns: \':not(:last-child)\'
                                }
                            }
                        ],
                        columnDefs: [{
                            orderable: false,
                            targets: -1
                        }]
                    });
                });
                '   
            ]
            
        ];
    }
    public function configIndex()
    {
        return $config = [
            'js' => [
                'js/device.js',
                'js/plugins/dataTables/datatables.min.js',
                'js/plugins/pace/pace.min.js',
                'js/plugins/footable/footable.all.min.js',
            ],
            'linkjs' => [],
            'css' => [
                'css/device.css',
                'css/plugins/dataTables/datatables.min.css',
                'css/plugins/footable/footable.core.css',
            ],
            'linkcss' => [],
            'script' => [
                '
                $(document).ready(function(){
                    var table = $(\'.dataTables-example\').DataTable({
                        pageLength: 10,
                        lengthChange: false,
                        responsive: true,
                        info: false,
                        ordering: true,
                        searching:false,
                        paging: true,
                    });
                });
                '   
            ]
            
        ];
    }
    public function index()
    {
        if (Auth::check()) {
            if (!Session::has('employee') && !Session::has('position_name')) {
                $authId = Auth::id();
                $employee = Employee::find($authId);
                $employee_id = $employee->employee_id;
                $position_name = Position::find($employee_id)->position_name;
                Session::put('employee', $employee);
                Session::put('position_name', $position_name);
            }
            $devices = Device::with('deviceType')->get();
            $data = ['devices' => $devices];
            $title = 'Device rental list';
            $employee = Session::get('employee');
            $position_name = Session::get('position_name');
            $config = $this->configIndex();
            $template = 'admin.device.devicerental.index';
    
            return view('admin.dashboard.layout', compact(
                'template',
                'config',
                'data',
                'title',
                'employee',
                'position_name'
            ));
        } else {
            return redirect()->route('auth.admin')->with('error', 'Vui lòng đăng nhập');
        }
    }
    public function createDeviceRental()
    {
        if (Auth::check()) {
            if (!Session::has('employee') && !Session::has('position_name')) {
                $authId = Auth::id();
                $employee = Employee::find($authId);
                $employee_id = $employee->employee_id;
                $position_name = Position::find($employee_id)->position_name;
                Session::put('employee', $employee);
                Session::put('position_name', $position_name);
            }
            $devices = Device::with('deviceType')->get();
            foreach ($devices as $device) {
                $rentalQuantity = DeviceRentalDetail::where('device_id', $device->device_id)->count();
                $device->rental_quantity = $rentalQuantity;
            }
            $data = ['devices' => $devices];
            $title = 'Device rental list';
            $employee = Session::get('employee');
            $position_name = Session::get('position_name');
            $config = $this->config();
            $template = 'admin.device.devicerental.createDeviceRental';
    
            return view('admin.dashboard.layout', compact(
                'template',
                'config',
                'data',
                'title',
                'employee',
                'position_name'
            ));
        } else {
            return redirect()->route('auth.admin')->with('error', 'Vui lòng đăng nhập');
        }
    }
    public function getRooms()
    {
        $rooms = Room::all(['room_id', 'room_name']);
        return response()->json(['rooms' => $rooms]);
    }  
    public function rent(Request $request)
    {
        $room_id = $request->input('room_id');
        $device_ids = $request->input('device_id');
        $rental_quantities = $request->input('rental_quantity');
        $deviceRental = new DeviceRental();
        $deviceRental->room_id = $room_id;
        $deviceRental->total_rental_price = $request->input('total_amount');
        $deviceRental->status = 'renting';
        $deviceRental->date_device_rental = now(); 
        $deviceRental->quantity = array_sum($rental_quantities); 
        $deviceRental->save();
        foreach ($device_ids as $key => $device_id) {
            $quantity = $rental_quantities[$key];
            for ($i = 0; $i < $quantity; $i++) {
                $rental_detail = new DeviceRentalDetail();
                $rental_detail->device_rental_id = $deviceRental->device_rental_id;
                $rental_detail->device_id = $device_id;
                $rental_detail->rental_price = $request->input('original_price')[$key] * 0.5;
                $rental_detail->status = 'Good';
                $rental_detail->save();
            }
        }
        return redirect()->back()->with('success', 'Thiết bị đã được cho thuê thành công.');
    }
}