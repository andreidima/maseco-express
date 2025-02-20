<?php

namespace App\Observers;

use App\Models\DocumentWord;
use App\Models\DocumentWordIstoric;
use Illuminate\Support\Facades\Auth;

class DocumentWordObserver
{
    public function created(DocumentWord $documentWord)
    {
        $this->logHistory($documentWord, 'Adaugare');
    }

    public function updated(DocumentWord $documentWord)
    {
        // Define fields that are not significant for history logging
        $ignoredFields = ['locked_by', 'locked_at', 'updated_at'];

        // Get all changes from the model update
        $changes = $documentWord->getChanges();

        // Remove the ignored fields from the changes
        foreach ($ignoredFields as $field) {
            unset($changes[$field]);
        }

        // If only ignored fields changed, skip logging
        if (empty($changes)) {
            return;
        }

        // Otherwise, log the update history
        $this->logHistory($documentWord, 'Modificare');
    }

    public function deleted(DocumentWord $documentWord)
    {
        $this->logHistory($documentWord, 'Stergere');
    }

    protected function logHistory(DocumentWord $documentWord, string $action)
    {
        $data = $documentWord
            ->makeHidden(['locked_by', 'locked_at', 'created_at', 'updated_at'])
            ->attributesToArray();

        // You could use mass assignment if your DocumentWordIstoric model has the proper $fillable.
        $data['operare_user_id']   = Auth::id();
        $data['operare_descriere'] = $action;

        DocumentWordIstoric::create($data);
    }
}
