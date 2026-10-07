<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class GuestImportService
{
    /**
     * Import guests from a CSV file (headers: name, phone, email, category, notes).
     *
     * @return array{
     *     imported: int,
     *     duplicates: int,
     *     invalid: list<array{row: int, reason: string}>,
     *     total: int,
     * }
     */
    public function import(Event $event, UploadedFile $file, User $actor): array
    {
        $result = ['imported' => 0, 'duplicates' => 0, 'invalid' => [], 'total' => 0];

        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            $result['invalid'][] = ['row' => 0, 'reason' => __('guests.import.unreadable')];

            return $result;
        }

        $header = fgetcsv($handle, 0, ',', '"', '\\');

        if ($header === false) {
            fclose($handle);
            $result['invalid'][] = ['row' => 1, 'reason' => __('guests.import.missing_header')];

            return $result;
        }

        $columns = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        if (! in_array('name', $columns, true)) {
            fclose($handle);
            $result['invalid'][] = ['row' => 1, 'reason' => __('guests.import.missing_name_column')];

            return $result;
        }

        $allowedCategories = array_map('strtolower', $event->guestCategories());
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $rowNumber++;

            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $result['total']++;

            $data = [];
            foreach ($columns as $i => $column) {
                $data[$column] = trim((string) ($row[$i] ?? ''));
            }

            $name = $data['name'] ?? '';
            $phone = $data['phone'] ?? '';
            $email = $data['email'] ?? '';
            $category = strtolower($data['category'] ?? '');

            if ($name === '') {
                $result['invalid'][] = ['row' => $rowNumber, 'reason' => __('guests.import.name_required')];

                continue;
            }

            if ($phone !== '' && ! preg_match('/^\+?[0-9\s\-()]{7,20}$/', $phone)) {
                $result['invalid'][] = ['row' => $rowNumber, 'reason' => __('guests.import.phone_invalid')];

                continue;
            }

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $result['invalid'][] = ['row' => $rowNumber, 'reason' => __('guests.import.email_invalid')];

                continue;
            }

            if ($category !== '' && ! in_array($category, $allowedCategories, true)) {
                $result['invalid'][] = ['row' => $rowNumber, 'reason' => __('guests.import.category_unknown')];

                continue;
            }

            // Advisory duplicate detection on normalized phone or email.
            $duplicate = false;

            if ($phone !== '') {
                $normalized = preg_replace('/\D+/', '', $phone);
                $duplicate = $event->guests()
                    ->whereRaw("replace(replace(replace(phone, '-', ''), ' ', ''), '+', '') = ?", [$normalized])
                    ->exists();
            }

            if (! $duplicate && $email !== '') {
                $duplicate = $event->guests()->whereRaw('lower(email) = ?', [strtolower($email)])->exists();
            }

            if ($duplicate) {
                $result['duplicates']++;

                continue;
            }

            Guest::create([
                'event_id' => $event->id,
                'name' => $name,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'category' => $category !== '' ? $category : null,
                'notes' => ($data['notes'] ?? '') !== '' ? $data['notes'] : null,
                'created_by' => $actor->id,
            ]);

            $result['imported']++;
        }

        fclose($handle);

        return $result;
    }
}
