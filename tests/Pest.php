<?php

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Taxonomies\LocalizedTerm;

pest()->extend(Tests\TestCase::class)->in('Feature');

function createLocalizedEntry(): EntryContract
{
    Collection::make('pages')->sites(['default', 'nl'])->save();

    return tap(Entry::make()->collection('pages')->locale('nl')->slug('page'))->save();
}

function createLocalizedTerm(): LocalizedTerm
{
    Taxonomy::make('topics')->sites(['default', 'nl'])->save();
    Term::make('topic')->taxonomy('topics')->set('title', 'Topic')->save();

    return tap(Term::find('topics::topic')->in('nl')->slug('onderwerp')->set('title', 'Onderwerp'))->save();
}
