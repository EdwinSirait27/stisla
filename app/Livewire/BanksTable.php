<?php

namespace App\Livewire;

use App\Models\Banks;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BanksExport;
use Rappasoft\LaravelLivewireTables\Views\Themes\Bootstrap4Theme;
class BanksTable extends DataTableComponent
{
//     protected $model = Banks::class;

//     public function configure(): void
//     {
//         $this->setPrimaryKey('id');
//         $this->setDefaultSort('id', 'asc');
//          $this->setPerPageAccepted([5, 10, 25, 50, 100, -1]);
//         $this->setPerPage(5);
//         $this->setSearchEnabled(); 
//             $this->setTheme('bootstrap-4');
//     }

//     public function columns(): array
//     {
//         return [
//             Column::make('ID', 'id')
//                 ->sortable()
//                 ->searchable(),

//             Column::make('Bank Name', 'name')
//                 ->sortable()
//                 ->searchable(),

//             Column::make('Action')
//                 ->label(
//                     fn($row) => view('components.table-actions', [
//                         'editUrl' => route('Banks.edit', substr(hash('sha256', $row->id . env('APP_KEY')), 0, 8)),
//                         'name' => $row->name,
//                     ])
//                 )
//                 ->html(),
//         ];
//     }

//     public function builder(): Builder
//     {
//         return Banks::query()
//             ->select(['id', 'name']);
//     }
//     public function exportExcel()
// {
//     return Excel::download(new BanksExport, 'banks.xlsx');
// }
}
