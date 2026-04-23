<?php

namespace App\Console\Commands;

use App\Models\DhRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SplitDhMultiAttachmentRecords extends Command
{
    protected $signature = 'dh:split-multi-attachments';
    protected $description = 'Split old DhRecord rows that contain multiple attachments into one record per attachment';

    public function handle(): int
    {
        $records = DhRecord::with(['attachments' => function ($q) {
            $q->orderBy('id');
        }])
        ->get()
        ->filter(function ($record) {
            return $record->attachments->count() > 1;
        });

        if ($records->isEmpty()) {
            $this->info('No multi-attachment records found.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($records) {
            foreach ($records as $record) {
                $attachments = $record->attachments->values();

                // keep first attachment on original record
                $attachmentsToMove = $attachments->slice(1);

                foreach ($attachmentsToMove as $att) {
                    $newRecord = DhRecord::create([
                        'folder_id' => $record->folder_id,
                        'doc_date' => $record->doc_date,
                        'description' => $record->description,
                        'created_at' => $record->created_at,
                        'updated_at' => now(),
                    ]);

                    $att->update([
                        'record_id' => $newRecord->id,
                    ]);
                }
            }
        });

        $this->info('Multi-attachment records were successfully split.');
        return self::SUCCESS;
    }
}