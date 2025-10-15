<?php

namespace App\Http\Controllers;
use App\Helper\Helpers;
use App\Models\EArchiveInvoicesOut;
use App\Models\InvoicesIn;
use App\Models\InvoicesOut;
use App\Models\SyncLog;
use App\Service\GetArchiveService;
use App\Service\GetInvoiceService;
use Carbon\Carbon;
use Symfony\Component\Process\Process;


use App\Models\cdEInvoiceWebService;
use App\Service\AuthService;
use Illuminate\Http\Request;
use Yajra\DataTables\Exceptions\Exception;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{

    public function __construct()
    {
        ini_set('max_execution_time', 0);
    }

    public function index(){

        return view('invoices.index');
    }

    public function indexIn(){

        return view('invoices.ininvoices');
    }

    public function indexArchive(){

        return view('invoices.archiveinvoices');
    }


    public function get_table_datas()
    {
        return view('invoices.index');
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function get_table_data()
    {
        $data = InvoicesOut::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('issue_date', fn($row) => \Carbon\Carbon::parse($row->issue_date)->format('Y-m-d'))
            ->editColumn('payable_amount', fn($row) => number_format((float) $row->payable_amount, 2, ',', '.') . ' ₺')
            ->orderColumn('payable_amount', function ($query, $order) {
                $query->orderByRaw('CAST(payable_amount AS DECIMAL(15,2)) ' . $order);
            })
            ->addColumn('status_badge', function ($row) {
                $color = $row->status_description === 'SUCCEED' ? 'success' : 'warning';
                return '<span class="badge bg-' . $color . '">' . e($row->status_description) . '</span>';
            })
            ->addColumn('actions', fn($row) => '<a href="/invoice/'.$row->id.'" class="btn btn-sm btn-dark">Görüntüle</a>')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function get_table_data_in(){
        $data = InvoicesIn::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('issue_date', fn($row) => \Carbon\Carbon::parse($row->issue_date)->format('Y-m-d'))
            ->editColumn('payable_amount', fn($row) => number_format((float) $row->payable_amount, 2, ',', '.') . ' ₺')
            ->orderColumn('payable_amount', function ($query, $order) {
                $query->orderByRaw('CAST(payable_amount AS DECIMAL(15,2)) ' . $order);
            })
            ->addColumn('status_badge', function ($row) {
                $color = $row->status_description === 'SUCCEED' ? 'success' : 'warning';
                return '<span class="badge bg-' . $color . '">' . e($row->status_description) . '</span>';
            })
            ->addColumn('actions', fn($row) => '<a href="/invoice/'.$row->id.'" class="btn btn-sm btn-dark">Görüntüle</a>')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);


    }



    public function get_table_data_archive(){
        $data = EArchiveInvoicesOut::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('issue_date', fn($row) => \Carbon\Carbon::parse($row->issue_date)->format('Y-m-d'))
            ->editColumn('payable_amount', fn($row) => number_format((float) $row->payable_amount, 2, ',', '.') . ' ₺')
            ->orderColumn('payable_amount', function ($query, $order) {
                $query->orderByRaw('CAST(payable_amount AS DECIMAL(15,2)) ' . $order);
            })
            ->addColumn('status_badge', function ($row) {
                $color = $row->status_description === 'SUCCEED' ? 'success' : 'warning';
                return '<span class="badge bg-' . $color . '">' . e($row->status_description) . '</span>';
            })
            ->addColumn('actions', fn($row) => '<a href="/invoice/'.$row->id.'" class="btn btn-sm btn-dark">Görüntüle</a>')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);


    }





    public static function SyncInvoices()
    {
        $users = Helpers::getUsers();
        foreach ($users as $user) {
            InvoiceController::getInvoice($user['UserName'],$user['Password'],$user['CompanyCode']);
        }
    }


    public static function getInvoice($username,$password,$companyCode){



        $session = AuthService::GetAuthToken($username,$password);
        $dates = InvoiceController::getLastMonth();
        $saveCount = 0;


        foreach($dates as $date){
            $dataInvoiceIn = GetInvoiceService::GetInvoice($session,$date,$date,'IN');
            $dataInvoiceOut = GetInvoiceService::GetInvoice($session,$date,$date,'OUT');
            $dataArchiveInvoice = GetArchiveService::getInvoice($session,$date,$date);
            $saveCount = GetInvoiceService::saveDb($dataInvoiceIn,'IN',$companyCode);
            $saveCount += getInvoiceService::saveDb($dataInvoiceOut,'OUT',$companyCode);
            $saveCount += getArchiveService::saveDb($dataArchiveInvoice,$companyCode);
        }

        SyncLog::create([
                'company_code' => $companyCode,
            ]
        );

        return $saveCount;
    }




    public static function getLastMonth(){
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subMonth();
        $dates = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) { // currentDate <= endDate
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }
        return $dates;
    }


    public function triggerSync()
    {
        $process = new Process([PHP_BINARY, base_path('artisan'), 'invoices:sync']);
        $process->setTimeout(0); // süresiz
        $process->start(); // non-blocking

        // process ilerleyişini isterseniz callback ile okuyabilirsiniz
        return response()->json(['status' => 'started']);
    }


    public function testtriggerSync() {
        try {
            $batFile = base_path('run_sync.bat');
            $command = "start /B \"\" \"$batFile\" > NUL 2>&1";

            pclose(popen("start /B cmd /C " . base_path('run_sync.bat'), "r"));

            return response()->json(['status' => 'started']);
        } catch (\Throwable $e) {
            \Log::error('Sync trigger hatası: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    public function testtriggerSyncs()
    {
        try {
            // Artisan komutu oluşturuluyor
            $process = new Process([
                PHP_BINARY,
                base_path('artisan'),
                'invoices:sync'
            ]);

            $process->setTimeout(0);
            $process->disableOutput();
            $process->start(); //

            return response()->json(['status' => 'started']);
        } catch (\Throwable $e) {
            \Log::error('Sync trigger hatası: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




}
