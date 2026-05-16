<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkingCampaignRequest;
use App\Models\Campaign;
use App\Services\Bulking\BulkingCampaignExecutor;
use App\Services\Bulking\BulkingCampaignSubmissionService;
use App\Services\Bulking\Exceptions\BulkingTargetParserException;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Throwable;

class BulkingController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsappService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $connection = $this->getConnectionStatus();
        return view('pesan.bulking', compact('connection'));
    }

    public function store(
        StoreBulkingCampaignRequest $request,
        BulkingCampaignSubmissionService $submissionService
    ) {
        try {
            $result = $submissionService->submit(
                payload: $request->validated(),
                targetFile: $request->file('excel_file'),
                mediaFile: $request->file('bulking_media'),
            );

            $campaign = $result['campaign'];
            $parserResult = $result['parser_result'];
            $invalidRows = count($parserResult['invalid_rows'] ?? []);
            $message = 'Campaign bulking berhasil dibuat dan dijadwalkan.';

            if ($invalidRows > 0) {
                $message .= ' ' . $invalidRows . ' target invalid dilewati saat parsing.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'campaign_id' => $campaign->getKey(),
                    'redirect_url' => route('bulking.show', $campaign),
                ]);
            }

            return redirect()
                ->route('bulking.show', $campaign)
                ->with('success', $message);
        } catch (BulkingTargetParserException $exception) {
            return $this->handleStoreFailure($request, $exception->getMessage(), 422);
        } catch (Throwable $throwable) {
            return $this->handleStoreFailure(
                $request,
                'Gagal memproses campaign bulking: ' . $throwable->getMessage(),
                500
            );
        }
    }

    public function log()
    {
        $campaigns = Campaign::query()
            ->latest()
            ->paginate(10);

        return view('pesan.bulking-log', compact('campaigns'));
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['pesans' => fn ($query) => $query->latest('id')]);

        return view('pesan.bulking-show', compact('campaign'));
    }

    public function pause(Campaign $campaign, BulkingCampaignExecutor $executor)
    {
        $executor->pauseCampaign($campaign);

        return back()->with('success', 'Campaign berhasil dijeda.');
    }

    public function resume(Campaign $campaign, BulkingCampaignExecutor $executor)
    {
        $executor->resumeCampaign($campaign);

        return back()->with('success', 'Campaign dilanjutkan.');
    }

    public function checkNumber(Request $request)
    {
        $request->validate(['number' => 'required']);

        try {
            $result = $this->whatsapp->checkNumber($request->number);

            return response()->json([
                'exists' => $result['registered'] ?? false,
                'message' => ($result['registered'] ?? false)
                    ? 'Nomor terdaftar di WhatsApp'
                    : 'Nomor tidak terdaftar di WhatsApp'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false,
                'message' => 'Gagal mengecek nomor: ' . $e->getMessage()
            ], 422);
        }
    }

    protected function getConnectionStatus()
    {
        try {
            $conn = $this->whatsapp->connection();
            if ($conn['status'] === 'ready') {
                return [
                    'connected' => true,
                    'status' => 'Terhubung',
                    'number' => $conn['info']['wid']['user'] ?? 'N/A'
                ];
            }
        } catch (\Exception $e) {}

        return [
            'connected' => false,
            'status' => 'Terputus',
            'number' => 'N/A'
        ];
    }

    protected function handleStoreFailure(Request $request, string $message, int $statusCode)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $statusCode);
        }

        return back()
            ->withErrors(['error' => $message])
            ->withInput();
    }
}
