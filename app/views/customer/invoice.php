<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Repair Job Sheet & Invoice') ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: var(--text-main);
            padding: 30px 15px;
            min-height: 100vh;
        }
        .action-bar {
            max-width: 860px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { background: #fff; color: var(--dark); border: 1px solid #cbd5e1; }
        .btn-outline:hover { background: #f1f5f9; }

        .invoice-card {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 48px;
            position: relative;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border);
            padding-bottom: 24px;
            margin-bottom: 28px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark);
        }
        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .company-address {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-code {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 4px 0;
            font-family: monospace;
        }
        .invoice-date {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .status-pill {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            background: #eff6ff;
            color: var(--primary);
            border: 1px solid #bfdbfe;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .info-box {
            background: var(--bg-light);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 20px;
        }
        .info-box h4 {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-box p {
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--dark);
        }
        .info-box strong {
            color: var(--dark);
        }

        .table-section {
            margin-bottom: 28px;
        }
        .table-section h3 {
            font-size: 1.1rem;
            margin-bottom: 12px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-table th {
            background: #f1f5f9;
            text-align: left;
            padding: 12px 16px;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
        }
        .invoice-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 0.92rem;
        }
        .text-right { text-align: right !important; }
        .invoice-table tfoot td {
            font-size: 1rem;
            padding: 14px 16px;
            border: none;
        }
        .grand-total-row td {
            font-size: 1.25rem !important;
            font-weight: 700;
            color: var(--primary);
            background: #eff6ff;
            border-top: 2px solid #bfdbfe !important;
        }

        .barcode-section {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            background: #fafafa;
        }
        .barcode-lines {
            font-family: 'Libre Barcode 39', monospace, sans-serif;
            font-size: 32px;
            letter-spacing: 4px;
            color: #0f172a;
            user-select: none;
        }
        .barcode-text {
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 40px;
            padding-top: 20px;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .terms-note {
            margin-top: 30px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.5;
            text-align: center;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
            .invoice-card {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="action-bar no-print">
    <a href="index.php?url=ticket/view/<?= htmlspecialchars($ticket['ticket_code']) ?>" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Back to Ticket
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fa-solid fa-print"></i> Print Job Sheet / Invoice
    </button>
</div>

<div class="invoice-card">
    <div class="invoice-header">
        <div>
            <div class="brand">
                <div class="brand-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <div>FixMy<span>Device</span></div>
            </div>
            <div class="company-address">
                <strong>FixMyDevice Hardware Services HQ</strong><br>
                100 Service HQ Blvd, Tech City<br>
                Hotline: +1 (800) 555-FIX-DEV | support@fixmydevice.com<br>
                Web: www.fixmydevice.com
            </div>
        </div>

        <div class="invoice-meta">
            <div class="invoice-title">Repair Job Sheet</div>
            <div class="invoice-code"><?= htmlspecialchars($ticket['ticket_code']) ?></div>
            <div class="invoice-date">Issue Date: <?= date('F d, Y') ?></div>
            <div class="invoice-date">Registered: <?= date('M d, Y', strtotime($ticket['created_at'])) ?></div>
            <div class="status-pill"><?= str_replace('_', ' ', strtoupper($ticket['status'])) ?></div>
        </div>
    </div>

    <!-- Customer & Appliance Details -->
    <div class="info-grid">
        <div class="info-box">
            <h4><i class="fa-solid fa-user"></i> Customer Details</h4>
            <p><strong><?= htmlspecialchars($ticket['customer_name']) ?></strong></p>
            <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($ticket['customer_email']) ?></p>
            <?php if (!empty($ticket['customer_phone'])): ?>
                <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($ticket['customer_phone']) ?></p>
            <?php endif; ?>
            <?php if (!empty($ticket['customer_address'])): ?>
                <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($ticket['customer_address']) ?></p>
            <?php endif; ?>
        </div>

        <div class="info-box">
            <h4><i class="fa-solid fa-tv"></i> Appliance Information</h4>
            <p><strong><?= htmlspecialchars($ticket['brand'] . ' ' . $ticket['device_name']) ?></strong></p>
            <p>Category: <strong><?= htmlspecialchars($ticket['category_name']) ?></strong></p>
            <p>Model / Serial: <?= htmlspecialchars(($ticket['model_number'] ?: 'N/A') . ' / ' . ($ticket['serial_number'] ?: 'N/A')) ?></p>
            <p>Warranty Status: <strong><?= strtoupper(str_replace('_', ' ', $ticket['warranty_status'])) ?></strong></p>
        </div>
    </div>

    <!-- Issue & Diagnosis Summary -->
    <div class="table-section">
        <h3><i class="fa-solid fa-clipboard-check"></i> Fault Summary & Scope of Work</h3>
        <div class="info-box mb-4">
            <p><strong>Reported Issue:</strong> <?= htmlspecialchars($ticket['issue_title']) ?></p>
            <p class="mt-2 text-muted"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
        </div>
    </div>

    <!-- Billing / Cost Breakdown -->
    <div class="table-section">
        <h3><i class="fa-solid fa-file-invoice-dollar"></i> Service Estimate & Parts Quotation</h3>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Category</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Rate / Est.</th>
                    <th class="text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $estTotal = floatval($ticket['estimated_cost'] ?? 0);
                $diagFee = ($estTotal > 0) ? min(250.00, $estTotal) : 0.00;
                $repairFee = max(0.00, $estTotal - $diagFee);
                ?>
                <tr>
                    <td>
                        <strong>Hardware Inspection & Diagnostics</strong><br>
                        <small class="text-muted">Multi-point electronic & electrical circuit test by certified technician</small>
                    </td>
                    <td>Labor / Service</td>
                    <td class="text-right">1</td>
                    <td class="text-right">₹<?= number_format($diagFee, 2) ?></td>
                    <td class="text-right">₹<?= number_format($diagFee, 2) ?></td>
                </tr>
                <tr>
                    <td>
                        <strong>Appliance Repair & Component Servicing</strong><br>
                        <small class="text-muted"><?= htmlspecialchars($ticket['brand'] . ' ' . $ticket['device_name']) ?> hardware repair</small>
                    </td>
                    <td>Technical Repair</td>
                    <td class="text-right">1</td>
                    <td class="text-right">₹<?= number_format($repairFee, 2) ?></td>
                    <td class="text-right">₹<?= number_format($repairFee, 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="text-right"><strong>Subtotal:</strong></td>
                    <td class="text-right"><strong>₹<?= number_format($estTotal, 2) ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                    <td class="text-right">Tax (0% Service):</td>
                    <td class="text-right">₹0.00</td>
                </tr>
                <tr class="grand-total-row">
                    <td colspan="3"><strong>Assigned Tech: <?= htmlspecialchars($ticket['technician_name'] ?: 'Pending Assignment') ?></strong></td>
                    <td class="text-right"><strong>Total Estimate:</strong></td>
                    <td class="text-right"><strong>₹<?= number_format($estTotal, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Barcode & Pickup Pass Section -->
    <div class="barcode-section">
        <div>
            <div style="font-weight:700;font-size:0.95rem;"><i class="fa-solid fa-qrcode"></i> Pickup Verification Pass</div>
            <div class="barcode-text">Show this barcode code at service counter for item release</div>
        </div>
        <div style="text-align:right;">
            <div style="font-family:monospace;font-size:1.3rem;font-weight:700;letter-spacing:3px;">
                |||| | ||||| ||| |||| || |||||
            </div>
            <div class="barcode-text"><?= htmlspecialchars($ticket['ticket_code']) ?></div>
        </div>
    </div>

    <!-- Signatures Section -->
    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-line">
                Customer Signature & Acceptance Date
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                Authorized Technician / Service Hub Stamp
            </div>
        </div>
    </div>

    <!-- Terms & Conditions Footer -->
    <div class="terms-note">
        FixMyDevice provides a 90-day service warranty on replaced components. Devices left uncollected after 30 days of notification may incur warehousing charges. Physical impact or liquid damage post-delivery is not covered under service warranty.
    </div>
</div>

</body>
</html>
