<?php

namespace App\Jobs;

use App\Support\Jobs\QueueableJob;

class QueueableDataExportJob extends QueueableJob
{
    use BaseDataExportJob;
}
