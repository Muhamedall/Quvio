<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $user    = User::where('email', 'john@quvio.com')->first();
        $clients = Client::where('user_id', $user->id)->get();

        // ── Invoice data spread across last 6 months ──────
        // This populates the monthly revenue chart beautifully
        $invoicesData = [

            // ── 5 months ago — paid ─────────────────────
            [
                'client'      => 'Acme Corp',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(5)->addDays(30),
                'paid_at'     => now()->subMonths(5)->addDays(25),
                'created_at'  => now()->subMonths(5),
                'notes'       => 'Website Phase 1 — Design',
                'items' => [
                    ['description' => 'UI/UX Design',        'quantity' => 1, 'unit_price' => 2500],
                    ['description' => 'Wireframes',          'quantity' => 1, 'unit_price' => 800],
                ],
            ],
            [
                'client'      => 'Bernard Consulting',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(5)->addDays(30),
                'paid_at'     => now()->subMonths(5)->addDays(20),
                'created_at'  => now()->subMonths(5)->addDays(5),
                'notes'       => 'SEO Audit Report',
                'items' => [
                    ['description' => 'SEO Audit',      'quantity' => 1, 'unit_price' => 900],
                    ['description' => 'Report Writing', 'quantity' => 1, 'unit_price' => 400],
                ],
            ],

            // ── 4 months ago — paid ─────────────────────
            [
                'client'      => 'TechStart',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(4)->addDays(30),
                'paid_at'     => now()->subMonths(4)->addDays(15),
                'created_at'  => now()->subMonths(4),
                'notes'       => 'Mobile App — Sprint 1',
                'items' => [
                    ['description' => 'React Native Dev', 'quantity' => 1, 'unit_price' => 3200],
                    ['description' => 'Code Review',      'quantity' => 4, 'unit_price' => 150],
                ],
            ],

            // ── 3 months ago — paid ─────────────────────
            [
                'client'      => 'Acme Corp',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(3)->addDays(30),
                'paid_at'     => now()->subMonths(3)->addDays(10),
                'created_at'  => now()->subMonths(3),
                'notes'       => 'Website Phase 2 — Development',
                'items' => [
                    ['description' => 'Frontend Development', 'quantity' => 1, 'unit_price' => 3500],
                    ['description' => 'CMS Integration',      'quantity' => 1, 'unit_price' => 1200],
                    ['description' => 'Testing',              'quantity' => 8, 'unit_price' => 100],
                ],
            ],
            [
                'client'      => 'Design Studio',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(3)->addDays(30),
                'paid_at'     => now()->subMonths(3)->addDays(28),
                'created_at'  => now()->subMonths(3)->addDays(5),
                'notes'       => 'Logo & Brand Identity',
                'items' => [
                    ['description' => 'Logo Design',      'quantity' => 1, 'unit_price' => 800],
                    ['description' => 'Brand Guidelines', 'quantity' => 1, 'unit_price' => 600],
                ],
            ],

            // ── 2 months ago — paid ─────────────────────
            [
                'client'      => 'TechStart',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(2)->addDays(30),
                'paid_at'     => now()->subMonths(2)->addDays(20),
                'created_at'  => now()->subMonths(2),
                'notes'       => 'Mobile App — Sprint 2',
                'items' => [
                    ['description' => 'React Native Dev',  'quantity' => 1, 'unit_price' => 3800],
                    ['description' => 'API Integration',   'quantity' => 1, 'unit_price' => 1200],
                ],
            ],
            [
                'client'      => 'Global Trading EU',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(2)->addDays(30),
                'paid_at'     => now()->subMonths(2)->addDays(5),
                'created_at'  => now()->subMonths(2)->addDays(2),
                'notes'       => 'Consulting — Q3',
                'items' => [
                    ['description' => 'Digital Strategy', 'quantity' => 1, 'unit_price' => 2200],
                    ['description' => 'Analytics Setup',  'quantity' => 1, 'unit_price' => 900],
                ],
            ],

            // ── 1 month ago — paid ──────────────────────
            [
                'client'      => 'Acme Corp',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(1)->addDays(30),
                'paid_at'     => now()->subMonths(1)->addDays(25),
                'created_at'  => now()->subMonths(1),
                'notes'       => 'Website Maintenance — October',
                'items' => [
                    ['description' => 'Monthly Maintenance', 'quantity' => 1, 'unit_price' => 500],
                    ['description' => 'Bug Fixes',           'quantity' => 3, 'unit_price' => 150],
                ],
            ],
            [
                'client'      => 'Bernard Consulting',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->subMonths(1)->addDays(30),
                'paid_at'     => now()->subMonths(1)->addDays(18),
                'created_at'  => now()->subMonths(1)->addDays(3),
                'notes'       => 'SEO Monthly Report',
                'items' => [
                    ['description' => 'SEO Optimization', 'quantity' => 1, 'unit_price' => 750],
                    ['description' => 'Content Writing',  'quantity' => 4, 'unit_price' => 200],
                ],
            ],

            // ── This month — mixed statuses ──────────────
            [
                'client'      => 'TechStart',
                'status'      => 'paid',
                'tax_rate'    => 20,
                'due_date'    => now()->addDays(15),
                'paid_at'     => now()->subDays(5),
                'created_at'  => now()->subDays(10),
                'notes'       => 'Mobile App — Sprint 3',
                'items' => [
                    ['description' => 'React Native Dev', 'quantity' => 1, 'unit_price' => 4200],
                    ['description' => 'QA Testing',       'quantity' => 8, 'unit_price' => 120],
                ],
            ],
            [
                'client'      => 'Emma Wilson',
                'status'      => 'unpaid',
                'tax_rate'    => 20,
                'due_date'    => now()->addDays(20),
                'paid_at'     => null,
                'created_at'  => now()->subDays(7),
                'notes'       => 'E-commerce Consulting',
                'items' => [
                    ['description' => 'Strategy Session',  'quantity' => 2, 'unit_price' => 350],
                    ['description' => 'Implementation',    'quantity' => 1, 'unit_price' => 1800],
                ],
            ],
            [
                'client'      => 'Design Studio',
                'status'      => 'unpaid',
                'tax_rate'    => 20,
                'due_date'    => now()->addDays(10),
                'paid_at'     => null,
                'created_at'  => now()->subDays(5),
                'notes'       => 'Social Media Assets',
                'items' => [
                    ['description' => 'Instagram Templates', 'quantity' => 10, 'unit_price' => 80],
                    ['description' => 'Banner Design',       'quantity' => 5,  'unit_price' => 120],
                ],
            ],
            [
                'client'      => 'Marc Lefebvre',
                'status'      => 'overdue',
                'tax_rate'    => 20,
                'due_date'    => now()->subDays(10),
                'paid_at'     => null,
                'created_at'  => now()->subDays(45),
                'notes'       => 'WordPress Development',
                'items' => [
                    ['description' => 'WordPress Setup',    'quantity' => 1, 'unit_price' => 600],
                    ['description' => 'Plugin Development', 'quantity' => 1, 'unit_price' => 900],
                ],
            ],
        ];

        $counter = 1;

        foreach ($invoicesData as $data) {
            // Find client
            $client = $clients->first(function ($c) use ($data) {
                return ($c->company === $data['client']) || ($c->name === $data['client']);
            });

            if (! $client) continue;

            // Generate invoice number
            $year          = now()->format('Y');
            $invoiceNumber = 'INV-' . $year . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;

            // Calculate totals
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $total    = $subtotal * (1 + $data['tax_rate'] / 100);

            // Create invoice
            $invoice = Invoice::create([
                'user_id'        => $user->id,
                'client_id'      => $client->id,
                'quote_id'       => null,
                'invoice_number' => $invoiceNumber,
                'status'         => $data['status'],
                'tax_rate'       => $data['tax_rate'],
                'subtotal'       => $subtotal,
                'total'          => $total,
                'due_date'       => $data['due_date'],
                'paid_at'        => $data['paid_at'],
                'notes'          => $data['notes'],
                'created_at'     => $data['created_at'],
                'updated_at'     => $data['created_at'],
            ]);

            // Create items
            foreach ($data['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'quote_id'    => null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }

        $this->command->info('✅ 13 invoices created (10 paid, 2 unpaid, 1 overdue)');
    }
}