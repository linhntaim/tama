<?php

namespace App\Jobs;

use App\Support\Jobs\QueueableJob;

class QueueableDataImportJob extends QueueableJob
{
    use BaseDataImportJob;
}
