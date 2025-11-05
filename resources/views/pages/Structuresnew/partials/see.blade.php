<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <tbody>
            <tr>
                <th width="25%">Company</th>
                {{-- <td>{{ $structure->company->name ?? '-' }}</td> --}}
            </tr>
            <tr>
                <th>Department</th>
                {{-- <td>{{ $structure->department->department_name ?? '-' }}</td> --}}
            </tr>
            <tr>
                <th>Location</th>
                {{-- <td>{{ $structure->store->name ?? '-' }}</td> --}}
            </tr>
            <tr>
                <th>Position</th>
                {{-- <td>{{ $structure->position->name ?? '-' }}</td> --}}
            </tr>
            <tr>
                <th>Hierarchy</th>
                {{-- <td>{{ $structure->parent->position->name ?? '-' }}</td> --}}
            </tr>
            <tr>
                <th>Is Manager?</th>
                {{-- <td>
                    @if ($structure->is_manager)
                        <span class="badge bg-success">Yes</span>
                    @else
                        <span class="badge bg-danger">No</span>
                    @endif
                </td> --}}
            </tr>
            <tr>
                <th>Role Summary</th>
                {{-- <td>{!! $structure->role_summary ?? '<em>Empty</em>' !!}</td> --}}
            </tr>
            <tr>
                <th>Key Responsibility</th>
                {{-- <td>{!! $structure->key_respon ?? '<em>Empty</em>' !!}</td> --}}
            </tr>
            <tr>
                <th>Qualifications</th>
                {{-- <td>{!! $structure->qualifications ?? '<em>Empty</em>' !!}</td> --}}
            </tr>
            <tr>
                <th>Salary</th>
                {{-- <td>{{ $structure->salary->salary_start ?? '-' }} to --}}
                    {{-- {{ $structure->salary->salary_end ?? '-' }}</td> --}}
            </tr>
            <tr>
                <th>Type</th>
                {{-- <td>
                    @forelse ($structure->type_badges as $badge)
                        <span class="badge bg-{{ $badge['color'] }}">{{ $badge['name'] }}</span>
                    @empty
                        <span class="text-muted">(empty)</span>
                    @endforelse
                </td> --}}
            </tr>
            <tr>
                <th>Created on date</th>
                {{-- <td>{{ $structure->created_at->format('d M Y, H:i') }}</td> --}}
            </tr>
            <tr>
                <th>Edited on date</th>
                {{-- <td>{{ $structure->updated_at->format('d M Y, H:i') }}</td> --}}
            </tr>
        </tbody>
    </table>
</div>
