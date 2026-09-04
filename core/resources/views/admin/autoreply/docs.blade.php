@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Header Overview Card -->
    <div class="col-12 mb-4">
        <div class="card bg--primary text-white shadow-sm border-0 rounded-3 p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-white text--primary fw-bold px-3 py-1 text-uppercase">REST API v1</span>
                        <span class="badge bg-success text-white px-2 py-1"><i class="las la-check-circle me-1"></i>Active & Ready</span>
                    </div>
                    <h3 class="text-white fw-bold mb-1">WhatsApp Message Sending API Documentation</h3>
                    <p class="text-white text-opacity-75 mb-0">
                        Easily integrate WhatsApp messaging into any external CRM, website, e-commerce store, webhook, or backend application.
                    </p>
                </div>
                <div class="d-flex flex-column align-items-end">
                    <div class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded p-2 px-3 text-end">
                        <small class="text-white text-opacity-75 d-block">Base API URL</small>
                        <span class="fw-bold font-monospace text-white">{{ $baseUrl }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation / Stats -->
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card p-3 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">Primary Connected Account</span>
                        <i class="lab la-whatsapp text--success fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">
                        {{ $primaryAccount ? $primaryAccount->account_name : 'No Account Connected' }}
                    </h5>
                    <small class="text-muted font-monospace">
                        {{ $primaryAccount && $primaryAccount->phone_number ? '+' . $primaryAccount->phone_number : 'Scan QR in Accounts' }}
                    </small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">Main Send Endpoint</span>
                        <i class="las la-paper-plane text--primary fs-4"></i>
                    </div>
                    <h6 class="fw-bold font-monospace text--primary mb-1 text-truncate">POST /api/send-message</h6>
                    <small class="text-muted">Supports Text, Images, PDF & Groups</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">Accounts Endpoint</span>
                        <i class="las la-list text--info fs-4"></i>
                    </div>
                    <h6 class="fw-bold font-monospace text--info mb-1 text-truncate">GET /api/accounts</h6>
                    <small class="text-muted">List all active WhatsApp accounts</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">Format & Authentication</span>
                        <i class="las la-shield-alt text--warning fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">JSON / Form-Data</h6>
                    <small class="text-muted">UTF-8 & Emoji Supported</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Documentation Tabs -->
    <div class="col-12">
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="apiDocTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold py-3 px-4 border-0 border-bottom border-primary border-3" id="tab-tester" data-bs-toggle="tab" data-bs-target="#content-tester" type="button">
                            <i class="las la-vial text--primary me-1 fs-5"></i> Live API Playground (Tester)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-3 px-4 text-dark border-0" id="tab-code" data-bs-toggle="tab" data-bs-target="#content-code" type="button">
                            <i class="las la-code text--info me-1 fs-5"></i> Code Examples & SDKs
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-3 px-4 text-dark border-0" id="tab-params" data-bs-toggle="tab" data-bs-target="#content-params" type="button">
                            <i class="las la-table text--success me-1 fs-5"></i> Parameters & Schema
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-3 px-4 text-dark border-0" id="tab-media" data-bs-toggle="tab" data-bs-target="#content-media" type="button">
                            <i class="las la-photo-video text--warning me-1 fs-5"></i> Media & Groups Guide
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="apiDocTabsContent">
                    
                    <!-- TAB 1: Interactive API Tester -->
                    <div class="tab-pane fade show active" id="content-tester" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="border rounded p-3 bg-light">
                                    <h5 class="fw-bold text-dark mb-1">
                                        <i class="las la-paper-plane text--primary me-1"></i> Send Real Test WhatsApp Message
                                    </h5>
                                    <p class="text-muted small mb-3">
                                        Test the live API endpoint directly from this page without writing any code.
                                    </p>

                                    <form id="apiTesterForm">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Target WhatsApp Account</label>
                                            <select id="test_session_id" class="form-select form-select-sm">
                                                <option value="">🌐 Default (Auto-picks Active Account)</option>
                                                @foreach($connectedAccounts as $acc)
                                                    <option value="{{ $acc->session_id }}" {{ $acc->status == 1 ? 'selected' : '' }}>
                                                        {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Active' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">
                                                Recipient Phone Number or Group JID <span class="text--danger">*</span>
                                            </label>
                                            <input type="text" id="test_receiver" class="form-control form-control-sm" placeholder="e.g. 923001234567 or 120363xxx@g.us" value="{{ $primaryAccount && $primaryAccount->phone_number ? $primaryAccount->phone_number : '' }}" required>
                                            <small class="text-muted text-xs">Enter full international format without + or spaces, or a WhatsApp Group ID.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">
                                                Message Text Content <span class="text--danger">*</span>
                                            </label>
                                            <textarea id="test_message" class="form-control form-control-sm" rows="3" placeholder="Hello from WhatsApp REST API! 🚀" required>Hello! This is a test message sent via the WhatsApp REST API. 🚀</textarea>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold small">Media / File URL (Optional)</label>
                                                <input type="url" id="test_media_url" class="form-control form-control-sm" placeholder="https://example.com/image.png">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Media Type</label>
                                                <select id="test_media_type" class="form-select form-select-sm">
                                                    <option value="text">None / Text</option>
                                                    <option value="image">Image</option>
                                                    <option value="video">Video</option>
                                                    <option value="document">Document (PDF/Zip)</option>
                                                    <option value="audio">Audio</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-2">
                                            <button type="button" id="btnSendTestApi" class="btn btn--primary btn-sm px-4 fw-bold">
                                                <i class="las la-paper-plane me-1"></i> Execute API Call
                                            </button>
                                            <button type="button" id="btnResetTester" class="btn btn-outline--secondary btn-sm px-3">
                                                Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Live Response Box -->
                            <div class="col-lg-6">
                                <div class="border rounded p-3 bg-dark text-light h-100 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary">
                                        <span class="fw-bold small text-info"><i class="las la-terminal me-1"></i> Live API Response</span>
                                        <span id="testResponseStatus" class="badge bg-secondary text-xs">Ready</span>
                                    </div>

                                    <div class="flex-grow-1 bg-black bg-opacity-50 rounded p-3 font-monospace small" style="min-height: 260px; max-height: 400px; overflow-y: auto;">
                                        <pre id="testResponseJson" class="text-white mb-0" style="white-space: pre-wrap;">// Click "Execute API Call" on the left to test sending live messages.
// The JSON response will appear here in real time.</pre>
                                    </div>

                                    <div class="mt-2 pt-2 border-top border-secondary d-flex align-items-center justify-content-between text-xs text-muted">
                                        <span>Target: <code>POST {{ $baseUrl }}/api/send-message</code></span>
                                        <button type="button" class="btn btn-xs btn-outline-light btn-copy" data-target="#testResponseJson">
                                            <i class="las la-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Code Examples & SDKs -->
                    <div class="tab-pane fade" id="content-code" role="tabpanel">
                        <div class="mb-3">
                            <h5 class="fw-bold text-dark mb-1">Code Snippets in Popular Languages</h5>
                            <p class="text-muted small">Copy and paste ready-to-use code snippets into your application.</p>
                        </div>

                        <!-- Language Nav -->
                        <ul class="nav nav-pills mb-3 gap-2" id="codeLangTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#code-curl" type="button">cURL (Bash)</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#code-php" type="button">PHP (cURL & Guzzle)</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#code-js" type="button">JavaScript / Node.js</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#code-python" type="button">Python (requests)</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#code-csharp" type="button">C# (.NET)</button>
                            </li>
                        </ul>

                        <div class="tab-content border rounded bg-dark p-3 text-light position-relative">
                            
                            <!-- cURL -->
                            <div class="tab-pane fade show active" id="code-curl" role="tabpanel">
                                <button class="btn btn-xs btn-outline-light position-absolute top-0 end-0 m-3 btn-copy" data-target="#snippet-curl">
                                    <i class="las la-copy me-1"></i> Copy
                                </button>
                                <pre id="snippet-curl" class="font-monospace text-info mb-0 small" style="white-space: pre-wrap;">curl -X POST {{ $baseUrl }}/api/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "receiver": "923001234567",
    "message": "Hello from Auto-WhatsApp REST API! 🚀"
  }'</pre>
                            </div>

                            <!-- PHP -->
                            <div class="tab-pane fade" id="code-php" role="tabpanel">
                                <button class="btn btn-xs btn-outline-light position-absolute top-0 end-0 m-3 btn-copy" data-target="#snippet-php">
                                    <i class="las la-copy me-1"></i> Copy
                                </button>
                                <pre id="snippet-php" class="font-monospace text-info mb-0 small" style="white-space: pre-wrap;">&lt;?php

// Using native cURL in PHP
$payload = [
    'receiver' => '923001234567',
    'message'  => 'Hello from PHP WhatsApp API! 🚀'
];

$ch = curl_init('{{ $baseUrl }}/api/send-message');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);</pre>
                            </div>

                            <!-- JavaScript -->
                            <div class="tab-pane fade" id="code-js" role="tabpanel">
                                <button class="btn btn-xs btn-outline-light position-absolute top-0 end-0 m-3 btn-copy" data-target="#snippet-js">
                                    <i class="las la-copy me-1"></i> Copy
                                </button>
                                <pre id="snippet-js" class="font-monospace text-info mb-0 small" style="white-space: pre-wrap;">// Using Modern Fetch (Browser / Node.js 18+)
const response = await fetch('{{ $baseUrl }}/api/send-message', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        receiver: '923001234567',
        message: 'Your verification OTP is: 849201 🔒'
    })
});

const data = await response.json();
console.log(data);</pre>
                            </div>

                            <!-- Python -->
                            <div class="tab-pane fade" id="code-python" role="tabpanel">
                                <button class="btn btn-xs btn-outline-light position-absolute top-0 end-0 m-3 btn-copy" data-target="#snippet-python">
                                    <i class="las la-copy me-1"></i> Copy
                                </button>
                                <pre id="snippet-python" class="font-monospace text-info mb-0 small" style="white-space: pre-wrap;">import requests

url = "{{ $baseUrl }}/api/send-message"
payload = {
    "receiver": "923001234567",
    "message": "Order #4928 confirmed! Thank you for your purchase."
}

response = requests.post(url, json=payload)
print(response.json())</pre>
                            </div>

                            <!-- C# -->
                            <div class="tab-pane fade" id="code-csharp" role="tabpanel">
                                <button class="btn btn-xs btn-outline-light position-absolute top-0 end-0 m-3 btn-copy" data-target="#snippet-csharp">
                                    <i class="las la-copy me-1"></i> Copy
                                </button>
                                <pre id="snippet-csharp" class="font-monospace text-info mb-0 small" style="white-space: pre-wrap;">using System.Net.Http;
using System.Text;
using System.Text.Json;

var client = new HttpClient();
var payload = new {
    receiver = "923001234567",
    message = "Hello from C# .NET!"
};

var content = new StringContent(JsonSerializer.Serialize(payload), Encoding.UTF8, "application/json");
var response = await client.PostAsync("{{ $baseUrl }}/api/send-message", content);
var result = await response.Content.ReadAsStringAsync();
Console.WriteLine(result);</pre>
                            </div>

                        </div>
                    </div>

                    <!-- TAB 3: Parameters & Schema -->
                    <div class="tab-pane fade" id="content-params" role="tabpanel">
                        <div class="mb-3">
                            <h5 class="fw-bold text-dark mb-1">Request Parameters Reference</h5>
                            <p class="text-muted small">Parameters can be sent via JSON payload or standard Form-Data.</p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Aliases</th>
                                        <th>Description & Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code class="text-primary fw-bold">receiver</code></td>
                                        <td><code>String / Array</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td><code>to</code>, <code>phone</code>, <code>number</code></td>
                                        <td>Recipient international phone number (e.g. <code>"923001234567"</code>), comma-separated list, or Group JID (e.g. <code>"120363xxx@g.us"</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-primary fw-bold">message</code></td>
                                        <td><code>String</code></td>
                                        <td><span class="badge bg-warning text-dark">Required*</span></td>
                                        <td><code>text</code>, <code>body</code>, <code>caption</code></td>
                                        <td>Text message body or media caption. Supports full multi-line text, emojis, and Urdu/Arabic.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-primary fw-bold">media_url</code></td>
                                        <td><code>String (URL)</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td><code>image_url</code>, <code>file_url</code>, <code>document_url</code></td>
                                        <td>Public web URL of the file/image/video/PDF to send.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-primary fw-bold">media_type</code></td>
                                        <td><code>String</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td><code>type</code></td>
                                        <td>Type of media: <code>text</code>, <code>image</code>, <code>video</code>, <code>audio</code>, <code>document</code>. Auto-detected from URL if omitted.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-primary fw-bold">filename</code></td>
                                        <td><code>String</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>-</td>
                                        <td>Custom filename displayed on WhatsApp for documents (e.g. <code>"Invoice_942.pdf"</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-primary fw-bold">session_id</code></td>
                                        <td><code>String</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td><code>account_phone</code>, <code>account_id</code></td>
                                        <td>Select specific WhatsApp account. If omitted, the system **automatically uses your active connected account**.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: Media & Groups Guide -->
                    <div class="tab-pane fade" id="content-media" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="las la-photo-video text--warning me-1"></i> Sending Images, Videos & PDFs
                                    </h6>
                                    <p class="text-muted small">
                                        To send media, provide the <code>media_url</code> parameter with a valid public image or document link:
                                    </p>
                                    <div class="bg-dark text-light p-3 rounded font-monospace small mb-2">
<pre class="mb-0 text-info" style="white-space: pre-wrap;">curl -X POST {{ $baseUrl }}/api/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "receiver": "923001234567",
    "media_url": "https://example.com/brochure.pdf",
    "media_type": "document",
    "filename": "Company_Brochure.pdf",
    "caption": "Here is our latest catalog!"
  }'</pre>
                                    </div>
                                    <small class="text-muted">Supported formats: JPG, PNG, WEBP, MP4, PDF, DOCX, XLSX, MP3, ZIP.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="las la-users text--primary me-1"></i> Sending to WhatsApp Groups
                                    </h6>
                                    <p class="text-muted small">
                                        To send an announcement to a WhatsApp group, pass the Group JID (ending with <code>@g.us</code>) as the <code>receiver</code>:
                                    </p>
                                    <div class="bg-dark text-light p-3 rounded font-monospace small mb-2">
<pre class="mb-0 text-info" style="white-space: pre-wrap;">curl -X POST {{ $baseUrl }}/api/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "receiver": "120363425319985290@g.us",
    "message": "🔥 Special group announcement for all members!"
  }'</pre>
                                    </div>
                                    <small class="text-muted">You can find Group JIDs in the <strong>Contacts & Groups</strong> section of the admin panel.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function($){
    "use strict";

    // Copy to clipboard helper
    $('.btn-copy').on('click', function(){
        const target = $($(this).data('target'));
        const text = target.text();
        navigator.clipboard.writeText(text).then(() => {
            notify('success', 'Copied to clipboard!');
        });
    });

    // Execute Live API Tester
    $('#btnSendTestApi').on('click', function(){
        const receiver = $('#test_receiver').val().trim();
        const message = $('#test_message').val().trim();
        const sessionId = $('#test_session_id').val();
        const mediaUrl = $('#test_media_url').val().trim();
        const mediaType = $('#test_media_type').val();

        if(!receiver){
            notify('error', 'Please enter a recipient phone number or group JID');
            return;
        }

        if(!message && !mediaUrl){
            notify('error', 'Please enter a message text or media URL');
            return;
        }

        $('#testResponseStatus').removeClass('bg-success bg-danger bg-secondary').addClass('bg-warning text-dark').text('Sending...');
        $('#testResponseJson').text('// Dispatching API request to WhatsApp server...');

        const payload = {
            receiver: receiver,
            message: message,
            session_id: sessionId,
            media_url: mediaUrl,
            media_type: mediaType
        };

        $.ajax({
            url: "{{ url('api/send-message') }}",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function(res){
                $('#testResponseStatus').removeClass('bg-warning text-dark bg-danger').addClass('bg-success text-white').text('HTTP 200 OK');
                $('#testResponseJson').text(JSON.stringify(res, null, 2));
                notify('success', 'Test message sent successfully!');
            },
            error: function(xhr){
                $('#testResponseStatus').removeClass('bg-warning text-dark bg-success').addClass('bg-danger text-white').text('HTTP ' + xhr.status);
                let errData;
                try {
                    errData = JSON.parse(xhr.responseText);
                } catch(e){
                    errData = { error: xhr.statusText, status: xhr.status };
                }
                $('#testResponseJson').text(JSON.stringify(errData, null, 2));
                notify('error', errData.message || 'API request failed');
            }
        });
    });

    $('#btnResetTester').on('click', function(){
        $('#test_message').val('');
        $('#test_media_url').val('');
        $('#test_media_type').val('text');
        $('#testResponseStatus').removeClass('bg-success bg-danger bg-warning').addClass('bg-secondary text-white').text('Ready');
        $('#testResponseJson').text('// Ready for testing.');
    });

})(jQuery);
</script>
@endpush
