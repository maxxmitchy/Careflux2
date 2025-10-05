<?php

namespace Src\Scraping\Domain\Enums;

enum ScrapeLogStatus: string
{
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
