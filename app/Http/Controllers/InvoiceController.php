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
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfDay();
        $stats = InvoicesOut::withoutGlobalScopes()
            ->whereBetween('issue_date', [$start, $end])
            ->selectRaw("COUNT(*) as total, SUM(payable_amount) as toplam, SUM(tax_inclusive_total_amount) as vergili, SUM(tax_exclusive_total_amount) as vergisiz")
            ->first();
        return view('invoices.index', compact('stats'));
    }

    public function indexIn(){
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfDay();
        $stats = InvoicesIn::withoutGlobalScopes()
            ->whereBetween('issue_date', [$start, $end])
            ->selectRaw("COUNT(*) as total, SUM(payable_amount) as toplam, SUM(tax_inclusive_total_amount) as vergili, SUM(tax_exclusive_total_amount) as vergisiz")
            ->first();
        return view('invoices.ininvoices', compact('stats'));
    }

    public function indexArchive(){
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfDay();
        $stats = EArchiveInvoicesOut::whereBetween('issue_date', [$start, $end])
            ->selectRaw("COUNT(*) as total, SUM(payable_amount) as toplam, SUM(taxable_amount) as vergisiz")
            ->first();
        return view('invoices.archiveinvoices', compact('stats'));
    }

    public function report(Request $request){
        $startDate = $request->start_date ?? null;
        $endDate   = $request->end_date   ?? null;

        // Gelen Fatura
        $gelenQuery = InvoicesIn::withoutGlobalScopes();
        if ($startDate) $gelenQuery->where('issue_date', '>=', $startDate . ' 00:00:00');
        if ($endDate)   $gelenQuery->where('issue_date', '<=', $endDate   . ' 23:59:59');
        $gelen = $gelenQuery->selectRaw("
            COUNT(*) as total,
            SUM(payable_amount) as toplam_odeme,
            SUM(tax_exclusive_total_amount) as vergisiz_tutar,
            SUM(tax_inclusive_total_amount) as vergili_tutar,
            SUM(line_extension_amount) as mal_hizmet_toplam,
            SUM(allowance_total_amount) as iskonto
        ")->first();

        // Giden Fatura
        $gidenQuery = InvoicesOut::withoutGlobalScopes();
        if ($startDate) $gidenQuery->where('issue_date', '>=', $startDate . ' 00:00:00');
        if ($endDate)   $gidenQuery->where('issue_date', '<=', $endDate   . ' 23:59:59');
        $giden = $gidenQuery->selectRaw("
            COUNT(*) as total,
            SUM(payable_amount) as toplam_odeme,
            SUM(tax_exclusive_total_amount) as vergisiz_tutar,
            SUM(tax_inclusive_total_amount) as vergili_tutar,
            SUM(line_extension_amount) as mal_hizmet_toplam,
            SUM(allowance_total_amount) as iskonto
        ")->first();

        // E-Arşiv Fatura
        $arsivQuery = EArchiveInvoicesOut::query();
        if ($startDate) $arsivQuery->where('issue_date', '>=', $startDate . ' 00:00:00');
        if ($endDate)   $arsivQuery->where('issue_date', '<=', $endDate   . ' 23:59:59');
        $arsiv = $arsivQuery->selectRaw("
            COUNT(*) as total,
            SUM(payable_amount) as toplam_odeme,
            SUM(taxable_amount) as vergisiz_tutar
        ")->first();

        return view('invoices.report', compact('gelen', 'giden', 'arsiv', 'startDate', 'endDate'));
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
            // Default: Son 1 ay (bugün hariç)
            $query->whereRaw("InvoiceDate >= DATEADD(MONTH, -1, CAST(GETDATE() AS date)) AND InvoiceDate < CAST(GETDATE() AS date)");
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
            // Default: Son 1 ay (bugün hariç)
            $query->whereRaw("IssueDate >= DATEADD(MONTH, -1, CAST(GETDATE() AS date)) AND IssueDate < CAST(GETDATE() AS date)");
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
            // Default: Son 1 ay (bugün hariç)
            $query->whereRaw("InvoiceDate >= DATEADD(MONTH, -1, CAST(GETDATE() AS date)) AND InvoiceDate < CAST(GETDATE() AS date)");
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
        $startDate = $endDate->copy()->subMonth();
        $dates = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
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
