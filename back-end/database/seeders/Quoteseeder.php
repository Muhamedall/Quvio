<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $user    = User::where('email', 'john@quvio.com')->first();
        $clients = Client::where('user_id', $user->id)->get();

        // ── Quote data with items ─────────────────────────
        $quotesData = [
            [
                'client'      => 'Acme Corp',
                'status'      => 'approved',
                'tax_rate'    => 20,
                'notes'       => 'Website redesign project — Phase 1.',
                'valid_until' => now()->addDays(30)->format('Y-m-d'),
                'created_at'  => now()->subDays(45),
                'items' => [
                    ['description' => 'UI/UX Design',          'quantity' => 1,   'unit_price' => 2500],
                    ['description' => 'Frontend Development',   'quantity' => 1,   'unit_price' => 3500],
                    ['description' => 'Project Management',     'quantity' => 10,  'unit_price' => 150],
                ],
            ],
            [
                'client'      => 'TechStart',
                'status'      => 'sent',
                'tax_rate'    => 20,
                'notes'       => 'Mobile app MVP development.',
                'valid_until' => now()->addDays(15)->format('Y-m-d'),
                'created_at'  => now()->subDays(10),
                'items' => [
                    ['description' => 'React Native Development', 'quantity' => 1, 'unit_price' => 4500],
                    ['description' => 'API Integration',          'quantity' => 1, 'unit_price' => 1200],
                    ['description' => 'Testing & QA',             'quantity' => 5, 'unit_price' => 200],
                ],
            ],
            [
                'client'      => 'Design Studio',
                'status'      => 'draft',
                'tax_rate'    => 20,
                'notes'       => 'Brand identity package.',
                'valid_until' => now()->addDays(30)->format('Y-m-d'),
                'created_at'  => now()->subDays(3),
                'items' => [
                    ['description' => 'Logo Design',        'quantity' => 1, 'unit_price' => 800],
                    ['description' => 'Brand Guidelines',   'quantity' => 1, 'unit_price' => 600],
                    ['description' => 'Business Cards',     'quantity' => 2, 'unit_price' => 150],
                ],
            ],
            [
                'client'      => 'Global Trading EU',
                'status'      => 'rejected',
                'tax_rate'    => 20,
                'notes'       => 'E-commerce platform — budget exceeded.',
                'valid_until' => now()->subDays(5)->format('Y-m-d'),
                'created_at'  => now()->subDays(20),
                'items' => [
                    ['description' => 'E-commerce Development', 'quantity' => 1, 'unit_price' => 8000],
                    ['description' => 'Payment Integration',    'quantity' => 1, 'unit_price' => 1500],
                ],
            ],
            [
                'client'      => 'Bernard Consulting',
                'status'      => 'approved',
                'tax_rate'    => 20,
                'notes'       => 'SEO audit and optimization.',
                'valid_until' => now()->addDays(30)->format('Y-m-d'),
                'created_at'  => now()->subDays(60),
                'items' => [
                    ['description' => 'SEO Audit',           'quantity' => 1,  'unit_price' => 900],
                    ['description' => 'Keyword Research',    'quantity' => 1,  'unit_price' => 400],
                    ['description' => 'Monthly SEO Report',  'quantity' => 3,  'unit_price' => 250],
                ],
            ],
        ];

        $counter = 1;

        foreach ($quotesData as $data) {
            // Find the client by company name
            $client = $clients->first(function ($c) use ($data) {
                return ($c->company === $data['client']) || ($c->name === $data['client']);
            });

            if (! $client) continue;

            // Generate quote number
            $year        = now()->format('Y');
            $quoteNumber = 'QUO-' . $year . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;

            // Calculate subtotal from items
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $total    = $subtotal * (1 + $data['tax_rate'] / 100);

            // Create the quote
            $quote = Quote::create([
                'user_id'      => $user->id,
                'client_id'    => $client->id,
                'quote_number' => $quoteNumber,
                'status'       => $data['status'],
                'tax_rate'     => $data['tax_rate'],
                'subtotal'     => $subtotal,
                'total'        => $total,
                'notes'        => $data['notes'],
                'valid_until'  => $data['valid_until'],
                'created_at'   => $data['created_at'],
                'updated_at'   => $data['created_at'],
            ]);

            // Create items for this quote
            foreach ($data['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                InvoiceItem::create([
                    'quote_id'    => $quote->id,
                    'invoice_id'  => null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => $itemSubtotal,
                ]);
            }
        }

        $this->command->info('✅ 5 quotes created with items');
    }
}