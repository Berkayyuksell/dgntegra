<?php

namespace App\Http\Controllers;
use App\Helper\Helpers;
use App\Models\AllInvoices;
use App\Models\EArchiveInvoicesOut;
use App\Models\InvoicesIn;
use App\Models\InvoicesOut;
use App\Models\SyncLog;
use App\Models\trInvoiceHeader;
use App\Service\GetArchiveService;
use App\Service\GetInvoiceHtmlService;
use App\Service\GetInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
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

    public function get_table_data(Request $request){
        $data = trInvoiceHeader::leftJoin('invoices_outs as v', 'v.uuid', '=', 'trInvoiceHeader.InvoiceHeaderID')
            ->where('trInvoiceHeader.IsReturn', '0')
            ->where('trInvoiceHeader.TransTypeCode', 2)
            ->where('trInvoiceHeader.InvoiceTypeCode', '1')
            ->select(
                'trInvoiceHeader.*',
                DB::raw("CASE WHEN v.uuid IS NULL THEN 0 ELSE 1 END AS isInvoiceOkey")
            )
            ->with(['V3AllInvoices', 'V3OutInvoices']);

        if ($request->has('isInvoiceOkey') && $request->isInvoiceOkey == '0') {
            $data->whereNull('v.uuid');
        }
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
            $data->whereBetween('trInvoiceHeader.InvoiceDate', [$start, $end]);
        } elseif (!empty($request->start_date)) {
            $data->whereDate('trInvoiceHeader.InvoiceDate', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $data->whereDate('trInvoiceHeader.InvoiceDate', '<=', $request->end_date);
        }

        return DataTables::of($data)
            ->editColumn('doc_price',fn($row)=> number_format($row->V3AllInvoices->first()->Doc_PriceVI,2,',','.') ?? '')
            ->addColumn('actions', fn($row) => '<a href="/invoice/'.$row->id.'" class="btn btn-sm btn-dark">Görüntüle</a>')
            ->addColumn('status_badge', function ($row) {
                $color = $row->isInvoiceOkey === '1' ? 'success' : 'danger';
                return '<span class="badge bg-' . $color . '">' . e($row->isInvoiceOkey ? 'Düşmüş' : 'E-Doganda Yok') . '</span>';
            })
            ->addColumn('customers',fn($row)=>$row->customer ?? '')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }



    public function get_table_data_in(Request $request){
        $data = InvoicesIn::leftjoin('e_InboxInvoiceHeader as v', 'v.UUID', '=', 'invoices_ins.uuid')
            ->select(
                'invoices_ins.*',
                DB::raw("CASE WHEN v.UUID IS NULL THEN 0 ELSE 1 END AS isInvoiceOkey")
            )
        ->with('V3InboxInvoiceHeader');

        if ($request->has('isInvoiceOkey') && $request->isInvoiceOkey == '0') {
            $data->whereNull('v.UUID');
        }
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
            $data->whereBetween('invoices_ins.cdate', [$start, $end]);
        } elseif (!empty($request->start_date)) {
            $data->whereDate('invoices_ins.cdate', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $data->whereDate('invoices_ins.cdate', '<=', $request->end_date);
        }


        return DataTables::of($data)
            ->addColumn('actions', fn($row) => '<a href="/invoice/'.$row->id.'" class="btn btn-sm btn-dark">Görüntüle</a>')
            ->addColumn('status_badge', function ($row) {
                $color = $row->isInvoiceOkey === '1' ? 'success' : 'danger';
                return '<span class="badge bg-' . $color . '">' . e($row->isInvoiceOkey ? 'Düşmüş' : 'E-Doganda Yok') . '</span>';
            })
            ->editColumn('cdate',fn($row)=> $row->cdate ? \Carbon\Carbon::parse($row->cdate)->format('Y-m-d')
                : '')
            ->addColumn('customers',fn($row)=>$row->V3OutInvoices->customer ?? '')
            ->editColumn('payable_amount',fn($row)=> number_format($row->payable_amount,2,',','.') ?? '')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);



    }





    public function get_table_data_archive(Request $request){
        $data = trInvoiceHeader::leftJoin('e_archive_invoices_outs as v', 'v.uuid', '=', 'trInvoiceHeader.InvoiceHeaderID')
            ->where('trInvoiceHeader.IsReturn', '0')
            ->where('trInvoiceHeader.TransTypeCode', 2)
            ->where('trInvoiceHeader.InvoiceTypeCode', '2')
            ->select(
                'trInvoiceHeader.*',
                DB::raw("CASE WHEN v.uuid IS NULL THEN 0 ELSE 1 END AS isInvoiceOkey")
            )
            ->with(['V3AllInvoices', 'V3OutInvoices']);

        if ($request->has('isInvoiceOkey') && $request->isInvoiceOkey == '0') {
            $data->whereNull('v.uuid');
        }
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
            $data->whereBetween('trInvoiceHeader.InvoiceDate', [$start, $end]);
        } elseif (!empty($request->start_date)) {
            $data->whereDate('trInvoiceHeader.InvoiceDate', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $data->whereDate('trInvoiceHeader.InvoiceDate', '<=', $request->end_date);
        }

        return DataTables::of($data)
            ->editColumn('doc_price',fn($row)=> number_format($row->V3AllInvoices->first()->Doc_PriceVI,2,'.',',') ?? '')
            ->addColumn('actions', fn($row) => '<a href="/invoice/'.$row->id.'" class="btn btn-sm btn-dark">Görüntüle</a>')
            ->addColumn('status_badge', function ($row) {
                $color = $row->isInvoiceOkey === '1' ? 'success' : 'danger';
                return '<span class="badge bg-' . $color . '">' . e($row->isInvoiceOkey ? 'Düşmüş' : 'E-Doganda Yok') . '</span>';
            })
            ->addColumn('customers',fn($row)=>$row->V3OutInvoices->customer ?? '')
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

    public function getHtml($invoiceId, $direction,$companyCode){
        $user = Helpers::getUser($companyCode);
        $session = AuthService::GetAuthToken($user['UserName'],$user['Password']);
        try {
            $service = new GetInvoiceHtmlService();
            $html = $service->getHtml($session, $invoiceId, $direction, $direction);

            return response($html, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }




    public function testtriggerSyncs()
    {
        try {
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
