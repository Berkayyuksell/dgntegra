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
        // view_e_invoices_out_panel kullanarak direkt veri çek
        $query = DB::table('view_e_invoices_out_panel');

        // Gitmeyen filtresi (status = 0)
        if ($request->has('isInvoiceOkey') && $request->isInvoiceOkey == '0') {
            $query->where('status', '0');
        }
        
        // Tarih filtresi - Default son 1 ay
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
            $query->whereBetween('InvoiceDate', [$start, $end]);
        } elseif (!empty($request->start_date)) {
            $query->whereDate('InvoiceDate', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $query->whereDate('InvoiceDate', '<=', $request->end_date);
        } else {
            // Default: Son 1 ay
            $query->whereRaw('InvoiceDate >= DATEADD(MONTH, -1, GETDATE())');
        }

        // Sıralama ve limit
        $query->orderBy('InvoiceDate', 'desc')
              ->limit(5000);

        // Tabulator için JSON formatı
        $results = $query->get()->map(function($row) {
            return [
                'id' => $row->InvoiceHeaderID,
                'InvoiceNumber' => $row->InvoiceNumber,
                'EInvoiceNumber' => $row->EInvoiceNumber,
                'customer' => $row->CurrAccDescription ?? '',
                'doc_price' => number_format($row->price ?? 0, 2, ',', '.'),
                'DocCurrencyCode' => 'TRY',
                'isInvoiceOkey' => $row->status,
                'status' => $row->status == '1' ? 'Düşmüş' : 'E-Doganda Yok',
                'status_color' => $row->status == '1' ? 'success' : 'danger',
                'InvoiceDate' => $row->InvoiceDate,
                'CompanyCode' => '1',
            ];
        });

        return response()->json($results);
    }



    public function get_table_data_in(Request $request){
        // view_e_invoices_in_panel kullanarak direkt veri çek
        $query = DB::table('view_e_invoices_in_panel');

        // Gitmeyen filtresi (status = 0)
        if ($request->has('isInvoiceOkey') && $request->isInvoiceOkey == '0') {
            $query->where('status', '0');
        }
        
        // Tarih filtresi - Default son 1 ay
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
            $query->whereBetween('IssueDate', [$start, $end]);
        } elseif (!empty($request->start_date)) {
            $query->whereDate('IssueDate', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $query->whereDate('IssueDate', '<=', $request->end_date);
        } else {
            // Default: Son 1 ay
            $query->whereRaw('IssueDate >= DATEADD(MONTH, -1, GETDATE())');
        }

        // Sıralama ve limit
        $query->orderBy('IssueDate', 'desc')
              ->limit(5000);

        // Tabulator için JSON formatı
        $results = $query->get()->map(function($row) {
            return [
                'id' => $row->UUID,
                'external_id' => $row->ID ?? '',
                'supplier' => $row->supplier ?? '',
                'payable_amount' => number_format($row->payable_amount ?? 0, 2, ',', '.'),
                'isInvoiceOkey' => $row->status,
                'status' => $row->status == '1' ? 'Düşmüş' : 'Nebimde Yok',
                'status_color' => $row->status == '1' ? 'success' : 'danger',
                'cdate' => $row->IssueDate ? \Carbon\Carbon::parse($row->IssueDate)->format('Y-m-d') : '',
            ];
        });

        return response()->json($results);
    }





    public function get_table_data_archive(Request $request){
        // view_e_archive_panel kullanarak direkt veri çek
        $query = DB::table('view_e_archive_panel');

        // Gitmeyen filtresi (status = 0)
        if ($request->has('isInvoiceOkey') && $request->isInvoiceOkey == '0') {
            $query->where('status', '0');
        }
        
        // Tarih filtresi - Default son 1 ay
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
            $query->whereBetween('InvoiceDate', [$start, $end]);
        } elseif (!empty($request->start_date)) {
            $query->whereDate('InvoiceDate', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $query->whereDate('InvoiceDate', '<=', $request->end_date);
        } else {
            // Default: Son 1 ay
            $query->whereRaw('InvoiceDate >= DATEADD(MONTH, -1, GETDATE())');
        }

        // Sıralama ve limit
        $query->orderBy('InvoiceDate', 'desc')
              ->limit(5000);

        // Tabulator için JSON formatı
        $results = $query->get()->map(function($row) {
            return [
                'id' => $row->InvoiceHeaderID,
                'InvoiceNumber' => $row->InvoiceNumber,
                'EInvoiceNumber' => $row->EInvoiceNumber,
                'customer' => $row->CurrAccDescription ?? '',
                'doc_price' => number_format($row->price ?? 0, 2, '.', ','),
                'DocCurrencyCode' => 'TRY',
                'isInvoiceOkey' => $row->status,
                'status' => $row->status == '1' ? 'Düşmüş' : 'E-Doganda Yok',
                'status_color' => $row->status == '1' ? 'success' : 'danger',
                'InvoiceDate' => $row->InvoiceDate,
            ];
        });

        return response()->json($results);
    }





    public static function SyncInvoices()
    {
        $users = Helpers::getUsers();
        $totalSynced = 0;
        
        foreach ($users as $user) {
            try {
                \Log::info("Senkronizasyon başlatılıyor: " . $user['CompanyCode']);
                $count = InvoiceController::getInvoice($user['UserName'],$user['Password'],$user['CompanyCode']);
                $totalSynced += $count;
                \Log::info("Senkronizasyon bitti: {$user['CompanyCode']} - {$count} kayıt");
            } catch (\Exception $e) {
                \Log::error("Şirket senkronizasyon hatası {$user['CompanyCode']}: " . $e->getMessage());
                // Hata olsa bile diğer şirketlere devam et
                continue;
            }
        }
        
        \Log::info("Toplam senkronizasyon tamamlandı: {$totalSynced} kayıt");
        return $totalSynced;
    }


    public static function getInvoice($username,$password,$companyCode){
        try {
            $session = AuthService::GetAuthToken($username,$password);
            
            if (!$session) {
                \Log::error("Login başarısız: {$companyCode}");
                return 0;
            }

            $dates = InvoiceController::getLastMonth();
            $saveCount = 0;

            foreach($dates as $date){
                try {
                    // Gelen faturalar
                    try {
                        $dataInvoiceIn = GetInvoiceService::GetInvoice($session,$date,$date,'IN');
                        $saveCount += GetInvoiceService::saveDb($dataInvoiceIn,'IN',$companyCode);
                    } catch (\Exception $e) {
                        \Log::warning("Gelen fatura çekme hatası [{$date}] {$companyCode}: " . $e->getMessage());
                    }

                    // Giden faturalar
                    try {
                        $dataInvoiceOut = GetInvoiceService::GetInvoice($session,$date,$date,'OUT');
                        $saveCount += GetInvoiceService::saveDb($dataInvoiceOut,'OUT',$companyCode);
                    } catch (\Exception $e) {
                        \Log::warning("Giden fatura çekme hatası [{$date}] {$companyCode}: " . $e->getMessage());
                    }

                    // E-Arşiv faturalar
                    try {
                        $dataArchiveInvoice = GetArchiveService::getInvoice($session,$date,$date);
                        $saveCount += GetArchiveService::saveDb($dataArchiveInvoice,$companyCode);
                    } catch (\Exception $e) {
                        \Log::warning("E-Arşiv fatura çekme hatası [{$date}] {$companyCode}: " . $e->getMessage());
                    }

                } catch (\Exception $e) {
                    \Log::warning("Tarih işleme hatası [{$date}] {$companyCode}: " . $e->getMessage());
                    continue; // Bu tarihi atla, diğer tarihlere devam et
                }
            }

            SyncLog::create([
                'company_code' => $companyCode,
            ]);

            \Log::info("Senkronizasyon tamamlandı: {$companyCode} - {$saveCount} kayıt");
            return $saveCount;

        } catch (\Exception $e) {
            \Log::error("Genel senkronizasyon hatası {$companyCode}: " . $e->getMessage());
            return 0;
        }
    }




    public static function getLastMonth(){
        $endDate = Carbon::today();
        //$startDate = $endDate->copy()->subMonth();
        $startDate = $endDate->copy()->subDays(2); // Son 2 gün
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
