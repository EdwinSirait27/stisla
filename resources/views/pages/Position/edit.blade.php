  @extends('layouts.app')
  @section('title', 'Update Position')
  @push('styles')
      <style>
          .form-control {
              border-radius: 8px;
              padding: 10px 15px;
              transition: all 0.3s ease;
              border: 1px solid #d1d1d1;
          }

          .form-control:focus {
              border-color: #6777ef;
              box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
          }

          .form-control-label {
              font-weight: 600;
              margin-bottom: 8px;
              color: #34395e;
              display: flex;
              align-items: center;
              gap: 8px;
          }

          .form-control-label i {
              color: #6777ef;
          }

          .card {
              border-radius: 15px;
              box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
              transition: all 0.3s ease;
          }

          .card:hover {
              box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.15);
          }

          .card-header {
              background-color: #fff;
              border-bottom: 1px solid #f9f9f9;
              padding: 20px;
          }

          .card-header h6 {
              font-weight: 700;
              font-size: 16px;
              color: #34395e;
          }

          .card-body {
              padding: 30px;
          }

          .btn {
              border-radius: 8px;
              padding: 10px 20px;
              font-weight: 600;
              transition: all 0.3s ease;
              margin-left: 10px;
          }

          .btn-secondary {
              background-color: #cdd3d8;
              border-color: #cdd3d8;
              color: #34395e;
          }

          .btn-secondary:hover {
              background-color: #b9bfc4;
              border-color: #b9bfc4;
          }

          .invalid-feedback {
              display: block;
              margin-top: 5px;
              font-size: 13px;
              color: #fc544b;
          }

          .alert-danger {
              background-color: #ffdede;
              border-color: #ffd0d0;
              color: #dc3545;
          }

          .alert-success {
              background-color: #d4edda;
              border-color: #c3e6cb;
              color: #155724;
          }

          /* Dynamic list styles */
          .responsibility-item {
              display: flex;
              align-items: center;
              gap: 8px;
              margin-bottom: 8px;
          }

          .responsibility-item input {
              flex: 1;
              margin-bottom: 0 !important;
          }

          .btn-remove-item {
              background: #fff;
              color: #fc544b;
              border: 1px solid #fc544b;
              border-radius: 6px;
              padding: 6px 10px;
              cursor: pointer;
              flex-shrink: 0;
              font-size: 12px;
              line-height: 1;
              width: 32px;
              height: 32px;
              display: flex;
              align-items: center;
              justify-content: center;
              transition: all 0.2s;
          }

          .btn-remove-item:hover {
              background: #fc544b;
              color: white;
          }

          .btn-add-item {
              background: #f4f6f9;
              border: 1px dashed #6777ef;
              color: #6777ef;
              border-radius: 8px;
              padding: 7px 16px;
              cursor: pointer;
              font-weight: 600;
              font-size: 13px;
              width: 100%;
              margin-top: 4px;
              transition: all 0.2s;
              display: flex;
              align-items: center;
              justify-content: center;
              gap: 6px;
          }

          .btn-add-item:hover {
              background: #eef0fd;
              border-color: #4a5fd4;
          }

          .section-label {
              font-weight: 700;
              font-size: 14px;
              color: #34395e;
              margin-bottom: 12px;
              display: flex;
              align-items: center;
              gap: 8px;
          }

          .divider {
              border-top: 1px solid #f1f2f3;
              margin: 24px 0;
          }

          .list-wrapper {
              background: #f8f9fc;
              border: 1px solid #eaeaea;
              border-radius: 10px;
              padding: 16px;
              margin-bottom: 8px;
          }
      </style>
  @endpush
  @section('main')
      <div class="main-content">
          <section class="section">
              <div class="section-header">
                  <h1>Update Position {{ $position->name }}</h1>
                  <div class="section-header-breadcrumb">
                      <div class="breadcrumb-item"><a href="{{ route('pages.Position') }}">Positions</a></div>
                      <div class="breadcrumb-item">Update Position {{ $position->name }}</div>
                  </div>
              </div>
              <div class="section-body">
                  <div class="container-fluid">
                      <div class="row">
                          <div class="col-12">
                              <div class="card">
                                  <div class="card-header pb-0 px-3">
                                      <h6 class="mb-0">{{ __('Update Position') }} {{ $position->name }}</h6>
                                  </div>
                                  <div class="card-body pt-4 p-3">
                                      @if ($errors->any())
                                          <div class="alert alert-danger">
                                              <ul class="mb-0">
                                                  @foreach ($errors->all() as $error)
                                                      <li>{{ $error }}</li>
                                                  @endforeach
                                              </ul>
                                          </div>
                                      @endif

                                      <form id="position-edit" action="{{ route('Position.update', $hashedId) }}"
                                          method="POST">
                                          @csrf
                                          @method('PUT')

                                          {{-- Position Name --}}
                                          {{-- <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label for="name" class="form-control-label">
                                                          <i class="fas fa-briefcase"></i> {{ __('Position Name') }}
                                                      </label>
                                                      <input type="text"
                                                          class="form-control @error('name') is-invalid @enderror"
                                                          id="name" name="name"
                                                          value="{{ old('name', $position->name) }}"
                                                          placeholder="e.g. Kasir, IT Support, HRD" required>
                                                      @error('name')
                                                          <span
                                                              class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                                      @enderror
                                                  </div>
                                              </div>
                                          </div> --}}
                                          <div class="row">

                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label for="name" class="form-control-label">
                                                          <i class="fas fa-briefcase"></i> {{ __('Position Name') }}
                                                      </label>

                                                      <input type="text"
                                                          class="form-control @error('name') is-invalid @enderror"
                                                          id="name" name="name"
                                                          value="{{ old('name', $position->name) }}"
                                                          placeholder="e.g. Kasir, IT Support, HRD" required>

                                                      @error('name')
                                                          <span class="invalid-feedback">
                                                              <strong>{{ $message }}</strong>
                                                          </span>
                                                      @enderror
                                                  </div>
                                              </div>


                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="form-control-label">
                                                          <i class="fas fa-globe"></i> {{ __('Publish Career') }}
                                                      </label>

                                                      <div class="custom-control custom-switch mt-2">
                                                          <input type="checkbox" class="custom-control-input"
                                                              id="publish_career" name="publish_career" value="1"
                                                              {{ old('publish_career', $position->publish_career) == 1 ? 'checked' : '' }}>

                                                          <label class="custom-control-label" for="publish_career">
                                                              Show on Job Career
                                                          </label>
                                                      </div>
                                                  </div>
                                              </div>

                                          </div>

                                          <div class="divider"></div>
                                          <div class="row">

                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label for="career_start_date" class="form-control-label">
                                                          <i class="fas fa-calendar-alt"></i>
                                                          Career Start Date
                                                      </label>

                                                      <input type="date"
                                                          class="form-control @error('career_start_date') is-invalid @enderror"
                                                          id="career_start_date" name="career_start_date"
                                                          value="{{ old('career_start_date', $position->career_start_date ? \Carbon\Carbon::parse($position->career_start_date)->format('Y-m-d') : '') }}">

                                                      @error('career_start_date')
                                                          <span class="invalid-feedback">
                                                              <strong>{{ $message }}</strong>
                                                          </span>
                                                      @enderror
                                                  </div>
                                              </div>


                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label for="career_end_date" class="form-control-label">
                                                          <i class="fas fa-calendar-alt"></i>
                                                          Career End Date
                                                      </label>

                                                      <input type="date"
                                                          class="form-control @error('career_end_date') is-invalid @enderror"
                                                          id="career_end_date" name="career_end_date"
                                                          value="{{ old('career_end_date', $position->career_end_date ? \Carbon\Carbon::parse($position->career_end_date)->format('Y-m-d') : '') }}">

                                                      @error('career_end_date')
                                                          <span class="invalid-feedback">
                                                              <strong>{{ $message }}</strong>
                                                          </span>
                                                      @enderror
                                                  </div>
                                              </div>

                                          </div>



                                          <div class="divider"></div>
                                          <div class="row">

                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label for="vacancy" class="form-control-label">
                                                          <i class="fas fa-users"></i>
                                                          Vacancy
                                                      </label>

                                                      <input type="number" min="1"
                                                          class="form-control @error('vacancy') is-invalid @enderror"
                                                          id="vacancy" name="vacancy"
                                                          value="{{ old('vacancy', $position->vacancy ?? 1) }}"
                                                          placeholder="e.g. 3">

                                                      @error('vacancy')
                                                          <span class="invalid-feedback">
                                                              <strong>{{ $message }}</strong>
                                                          </span>
                                                      @enderror
                                                  </div>
                                              </div>

                                          </div>

                                          <div class="divider"></div>



                                          {{-- Role Summary --}}
                                          <div class="form-group">
                                              <label for="role_summary" class="form-control-label">
                                                  <i class="fas fa-align-left"></i> {{ __('Role Summary') }}
                                              </label>
                                              <textarea class="form-control @error('role_summary') is-invalid @enderror" id="role_summary" name="role_summary"
                                                  rows="3" placeholder="Deskripsi singkat peran posisi ini...">{{ old('role_summary', $position->role_summary) }}</textarea>
                                              @error('role_summary')
                                                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                              @enderror
                                          </div>

                                          <div class="divider"></div>

                                          {{-- Key Responsibilities --}}
                                          <div class="form-group">
                                              <div class="section-label">
                                                  <i class="fas fa-tasks mr-2" style="color:#6777ef"></i> Key
                                                  Responsibilities
                                              </div>
                                              <div id="key-respon-list">
                                                  @php
                                                      $keyResponItems = old('key_respon')
                                                          ? collect(old('key_respon'))
                                                          : $position->responsibilities->pluck('description');
                                                  @endphp
                                                  @forelse($keyResponItems as $item)
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="key_respon[]"
                                                              value="{{ $item }}"
                                                              placeholder="Tanggung jawab...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @empty
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="key_respon[]"
                                                              placeholder="Tanggung jawab...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @endforelse
                                              </div>
                                              <button type="button" class="btn-add-item"
                                                  onclick="addItem('key-respon-list', 'key_respon[]', 'Tanggung jawab...')">
                                                  <i class="fas fa-plus"></i> Add Responsibility
                                              </button>
                                          </div>

                                          <div class="divider"></div>

                                          {{-- Qualifications --}}
                                          <div class="form-group">
                                              <div class="section-label">
                                                  <i class="fas fa-graduation-cap mr-2" style="color:#6777ef"></i>
                                                  Qualifications
                                              </div>
                                              <div id="qualification-list">
                                                  @php
                                                      $qualificationItems = old('qualification')
                                                          ? collect(old('qualification'))
                                                          : $position->qualifications->pluck('description');
                                                  @endphp
                                                  @forelse($qualificationItems as $item)
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control"
                                                              name="qualification[]" value="{{ $item }}"
                                                              placeholder="Kualifikasi...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @empty
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control"
                                                              name="qualification[]" placeholder="Kualifikasi...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @endforelse
                                              </div>
                                              <button type="button" class="btn-add-item"
                                                  onclick="addItem('qualification-list', 'qualification[]', 'Kualifikasi...')">
                                                  <i class="fas fa-plus"></i> Add Qualification
                                              </button>
                                          </div>

                                          <div class="divider"></div>

                                          {{-- Benefit yoo --}}
                                          <div class="form-group">
                                              <div class="section-label">
                                                  <i class="fas fa-graduation-cap mr-2" style="color:#6777ef"></i>
                                                  Benefits
                                              </div>
                                              <div id="benefit-list">
                                                  @php
                                                      $benefitItems = old('benefit')
                                                          ? collect(old('benefit'))
                                                          : $position->benefits->pluck('description');
                                                  @endphp
                                                  @forelse($benefitItems as $item)
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="benefit[]"
                                                              value="{{ $item }}"
                                                              placeholder="keuntungan melamar...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @empty
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="benefit[]"
                                                              placeholder="keuntungan melamar...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @endforelse
                                              </div>
                                              <button type="button" class="btn-add-item"
                                                  onclick="addItem('benefit-list', 'benefit[]', 'keuntungan melamar...')">
                                                  <i class="fas fa-plus"></i> Add Benefit
                                              </button>
                                          </div>

                                          <div class="divider"></div>


                                          {{-- Benefit yoo --}}
                                          <div class="form-group">
                                              <div class="section-label">
                                                  <i class="fas fa-graduation-cap mr-2" style="color:#6777ef"></i>
                                                  Requirements
                                              </div>
                                              <div id="requirement-list">
                                                  @php
                                                      $requirementItems = old('requirement')
                                                          ? collect(old('requirement'))
                                                          : $position->requirements->pluck('description');
                                                  @endphp
                                                  @forelse($requirementItems as $item)
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="requirement[]"
                                                              value="{{ $item }}" placeholder="requirement...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @empty
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="requirement[]"
                                                              placeholder="requirement...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @endforelse
                                              </div>
                                              <button type="button" class="btn-add-item"
                                                  onclick="addItem('requirement-list', 'requirement[]', 'requirement...')">
                                                  <i class="fas fa-plus"></i> Add Requirement
                                              </button>
                                          </div>

                                          <div class="divider"></div>

                                          <div class="form-group">
                                              <div class="section-label">
                                                  <i class="fas fa-graduation-cap mr-2" style="color:#6777ef"></i>
                                                  Skills
                                              </div>
                                              <div id="skill-list">
                                                  @php
                                                      $skillItems = old('skill')
                                                          ? collect(old('skill'))
                                                          : $position->skills->pluck('description');
                                                  @endphp
                                                  @forelse($skillItems as $item)
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="skill[]"
                                                              value="{{ $item }}" placeholder="skill...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @empty
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="skill[]"
                                                              placeholder="skill...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @endforelse
                                              </div>
                                              <button type="button" class="btn-add-item"
                                                  onclick="addItem('skill-list', 'skill[]', 'skill...')">
                                                  <i class="fas fa-plus"></i> Add skill
                                              </button>
                                          </div>

                                          <div class="divider"></div>

                                          <div class="form-group">
                                              <div class="section-label">
                                                  <i class="fas fa-graduation-cap mr-2" style="color:#6777ef"></i>
                                                  Allowances
                                              </div>
                                              <div id="allowance-list">
                                                  @php
                                                      $allowanceItems = old('allowance')
                                                          ? collect(old('allowance'))
                                                          : $position->allowances->pluck('description');
                                                  @endphp
                                                  @forelse($allowanceItems as $item)
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="allowance[]"
                                                              value="{{ $item }}" placeholder="allowance...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @empty
                                                      <div class="responsibility-item">
                                                          <input type="text" class="form-control" name="allowance[]"
                                                              placeholder="allowance...">
                                                          <button type="button" class="btn-remove-item"
                                                              onclick="removeItem(this)">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                  @endforelse
                                              </div>
                                              <button type="button" class="btn-add-item"
                                                  onclick="addItem('allowance-list', 'allowance[]', 'allowance...')">
                                                  <i class="fas fa-plus"></i> Add allowance
                                              </button>
                                          </div>

                                          <div class="divider"></div>



                                          <div class="d-flex justify-content-end mt-4">
                                              <a href="{{ route('pages.Position') }}" class="btn btn-secondary">
                                                  <i class="fas fa-times"></i> {{ __('Cancel') }}
                                              </a>
                                              <button type="button" id="edit-btn" class="btn bg-primary">
                                                  <i class="fas fa-save"></i> {{ __('Update') }}
                                              </button>
                                          </div>
                                      </form>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </section>
      </div>
  @endsection
  @push('scripts')
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
          function addItem(listId, inputName, placeholder) {
              const list = document.getElementById(listId);
              const div = document.createElement('div');
              div.className = 'responsibility-item';
              div.innerHTML = `
                <input type="text" class="form-control" name="${inputName}" placeholder="${placeholder}">
                <button type="button" class="btn-remove-item" onclick="removeItem(this)">
                    <i class="fas fa-times"></i>
                </button>`;
              list.appendChild(div);
              div.querySelector('input').focus();
          }

          function removeItem(btn) {
              const list = btn.closest('[id$="-list"]');
              const items = list.querySelectorAll('.responsibility-item');
              if (items.length > 1) {
                  btn.closest('.responsibility-item').remove();
              } else {
                  btn.closest('.responsibility-item').querySelector('input').value = '';
              }
          }

          document.getElementById('edit-btn').addEventListener('click', function(e) {
              e.preventDefault();
              Swal.fire({
                  title: 'Are You Sure?',
                  text: "Make sure the data you entered is correct!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, Update!',
                  cancelButtonText: 'Abort'
              }).then((result) => {
                  if (result.isConfirmed) {
                      document.getElementById('position-edit').submit();
                  }
              });
          });

          @if (session('success'))
              Swal.fire({
                  title: 'Berhasil!',
                  text: "{{ session('success') }}",
                  icon: 'success',
                  confirmButtonText: 'OK'
              });
          @endif
          @if (session('error'))
              Swal.fire({
                  title: 'Gagal!',
                  text: "{{ session('error') }}",
                  icon: 'error',
                  confirmButtonText: 'OK'
              });
          @endif
      </script>
  @endpush
