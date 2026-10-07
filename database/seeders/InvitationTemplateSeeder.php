<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use Illuminate\Database\Seeder;

class InvitationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Elegant Luxury',
                'style' => 'elegant',
                'config' => [
                    'background' => '#12261f',
                    'surface' => '#1b3a30',
                    'text' => '#f5efe2',
                    'muted' => '#c9bfa8',
                    'accent' => '#d4af37',
                    'heading_font' => 'Playfair Display, Georgia, serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'diamond',
                ],
            ],
            [
                'name' => 'Minimalist',
                'style' => 'minimalist',
                'config' => [
                    'background' => '#ffffff',
                    'surface' => '#fafafa',
                    'text' => '#18181b',
                    'muted' => '#71717a',
                    'accent' => '#18181b',
                    'heading_font' => 'Instrument Sans, system-ui, sans-serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'line',
                ],
            ],
            [
                'name' => 'Floral',
                'style' => 'floral',
                'config' => [
                    'background' => '#fdf7f2',
                    'surface' => '#ffffff',
                    'text' => '#4a2f28',
                    'muted' => '#9c7a6d',
                    'accent' => '#c25e6b',
                    'heading_font' => 'Playfair Display, Georgia, serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'floral',
                ],
            ],
            [
                'name' => 'Traditional African',
                'style' => 'traditional',
                'config' => [
                    'background' => '#fbf3e4',
                    'surface' => '#fffaf0',
                    'text' => '#3e2a12',
                    'muted' => '#8a6d3b',
                    'accent' => '#b4541e',
                    'heading_font' => 'Playfair Display, Georgia, serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'kente',
                ],
            ],
            [
                'name' => 'Modern',
                'style' => 'modern',
                'config' => [
                    'background' => '#0b1220',
                    'surface' => '#131c2e',
                    'text' => '#eef2ff',
                    'muted' => '#94a3b8',
                    'accent' => '#38bdf8',
                    'heading_font' => 'Instrument Sans, system-ui, sans-serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'geometric',
                ],
            ],
            [
                'name' => 'Formal Corporate',
                'style' => 'corporate',
                'config' => [
                    'background' => '#ffffff',
                    'surface' => '#f4f6f8',
                    'text' => '#1f2937',
                    'muted' => '#6b7280',
                    'accent' => '#1e3a5f',
                    'heading_font' => 'Instrument Sans, system-ui, sans-serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'line',
                ],
            ],
            [
                'name' => 'Colorful Celebration',
                'style' => 'colorful',
                'config' => [
                    'background' => '#fff8ec',
                    'surface' => '#ffffff',
                    'text' => '#3b2a52',
                    'muted' => '#8b7aa8',
                    'accent' => '#f59e0b',
                    'heading_font' => 'Playfair Display, Georgia, serif',
                    'body_font' => 'Instrument Sans, system-ui, sans-serif',
                    'ornament' => 'confetti',
                ],
            ],
        ];

        foreach ($templates as $template) {
            InvitationTemplate::updateOrCreate(
                ['scope' => 'platform', 'style' => $template['style']],
                [
                    'name' => $template['name'],
                    'category' => null,
                    'config' => $template['config'],
                    'is_active' => true,
                ],
            );
        }
    }
}
