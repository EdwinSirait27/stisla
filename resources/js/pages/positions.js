// import $ from 'jquery';
// import 'datatables.net';
// import Swal from 'sweetalert2';

// document.addEventListener('DOMContentLoaded', () => {
//     const tableElement = $('#users-table');

//     if (tableElement.length) {
//         // ✅ Initialize DataTable
//         const table = tableElement.DataTable({
//             processing: true,
//             serverSide: true,
//             ajax: {
//                 url: window.routes.positions, // route dari Blade
//                 type: 'GET'
//             },
//             responsive: true,
//             lengthMenu: [
//                 [10, 25, 50, 100, -1],
//                 [10, 25, 50, 100, "All"]
//             ],
//             language: {
//                 search: "_INPUT_",
//                 searchPlaceholder: "Search...",
//             },
//             columns: [
//                 {
//                     data: null,
//                     name: 'id',
//                     className: 'text-center align-middle',
//                     render: function (data, type, row, meta) {
//                         return meta.row + meta.settings._iDisplayStart + 1;
//                     }
//                 },
//                 {
//                     data: 'name',
//                     name: 'name',
//                     className: 'text-center'
//                 },
//                 {
//                     data: 'action',
//                     name: 'action',
//                     orderable: false,
//                     searchable: false,
//                     className: 'text-center'
//                 }
//             ],
//             initComplete: function () {
//                 $('.dataTables_filter input').addClass('form-control');
//                 $('.dataTables_length select').addClass('form-control');
//             }
//         });

//         // ✅ SweetAlert success popup (jika ada session success)
//         if (window.sessionSuccess) {
//             Swal.fire({
//                 icon: 'success',
//                 title: 'Success',
//                 text: window.sessionSuccess,
//             });
//         }
//     }
// });
// import 'datatables.net';
// import Swal from 'sweetalert2';

// $(document).ready(function () {
//     const table = $('#users-table').DataTable({
//         processing: true,
//         serverSide: true,
//         ajax: {
//                     url: '{{ route('positions.positions') }}',

//             type: 'GET'
//         },
//         responsive: true,
//         lengthMenu: [
//             [10, 25, 50, 100, -1],
//             [10, 25, 50, 100, "All"]
//         ],
//         language: {
//             search: "_INPUT_",
//             searchPlaceholder: "Search...",
//         },
//         columns: [
//             {
//                 data: null,
//                 render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1,
//                 className: 'text-center align-middle'
//             },
//             { data: 'name', className: 'text-center' },
//             { data: 'action', orderable: false, searchable: false, className: 'text-center' }
//         ],
//     });

//     if (window.successMessage) {
//         Swal.fire({
//             icon: 'success',
//             title: 'Success',
//             text: window.successMessage,
//         });
//     }
// });
import $ from 'jquery';
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import Swal from 'sweetalert2';

$(document).ready(function () {
    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('positions.positions') }}', // ✅ langsung bisa karena Mix
            type: 'GET'
        },
        responsive: true,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        },
        columns: [
            {
                data: null,
                render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1,
                className: 'text-center align-middle'
            },
            { data: 'name', className: 'text-center' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
    });

    // ✅ SweetAlert2 untuk notifikasi
    if (window.successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: window.successMessage,
        });
    }
});


