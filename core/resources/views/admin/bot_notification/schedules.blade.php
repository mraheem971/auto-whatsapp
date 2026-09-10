@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
            <div>
                <h4 class="mb-1">Scheduled Notifications & Reminders</h4>
                <p class="text-muted mb-0">Automate recurring reminders, daily updates, and scheduled WhatsApp broadcasts.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bot.notifications.index') }}" class="btn btn-outline--dark btn-sm">
                    <i class="las la-arrow-left me-1"></i> Dashboard
                </a>
                <button type="button" class="btn btn--primary btn-sm" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                    <i class="las la-plus-circle me-1"></i> Create Scheduled Task
                </button>
            </div>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="las la-calendar-alt text--primary me-1"></i> Scheduled Task List ({{ $tasks->total() }})
                </h6>
                <div class="text-muted small">
                    <i class="las la-info-circle me-1"></i> Run via cron: <code>{{ url('/api/notifications/cron/run') }}</code>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Task Title</th>
                                <th>Event & Type</th>
                                <th>Target Audience</th>
                                <th>Recurrence / Schedule</th>
                                <th>Next Run</th>
                                <th>Sent / Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $task->title }}</div>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                            {{ $task->message }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-capitalize mb-1">{{ $task->event_type }}</span>
                                        @if($task->media_type && $task->media_type !== 'text')
                                            <div><span class="badge bg-info text-capitalize">{{ $task->media_type }}</span></div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', ' ', $task->target_type)) }}
                                        </span>
                                        @if($task->target_identifier)
                                            <div class="small text-muted">{{ $task->target_identifier }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-uppercase">{{ $task->schedule_type }}</span>
                                        <div class="small text-muted">
                                            @if($task->schedule_type === 'once')
                                                {{ $task->schedule_datetime ? showDateTime($task->schedule_datetime) : 'N/A' }}
                                            @elseif($task->schedule_type === 'daily')
                                                Daily at {{ $task->schedule_time ?? '09:00' }}
                                            @elseif($task->schedule_type === 'weekly')
                                                Every {{ $task->schedule_day_of_week ?? 'Monday' }} at {{ $task->schedule_time ?? '09:00' }}
                                            @elseif($task->schedule_type === 'monthly')
                                                Day {{ $task->schedule_day_of_month ?? '1' }} at {{ $task->schedule_time ?? '09:00' }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->next_run_at)
                                            <span class="fw-bold text-dark">{{ showDateTime($task->next_run_at) }}</span>
                                            <div class="small text-muted">{{ \Carbon\Carbon::parse($task->next_run_at)->diffForHumans() }}</div>
                                        @else
                                            <span class="text-muted">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            @if($task->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @elseif($task->status == 2)
                                                <span class="badge bg-secondary">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Paused</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">Sent: <strong>{{ $task->total_sent_count }}</strong> times</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Toggle Status -->
                                            <form action="{{ route('admin.bot.notifications.toggle.schedule', $task->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $task->status == 1 ? 'warning' : 'success' }}" title="{{ $task->status == 1 ? 'Pause Task' : 'Activate Task' }}">
                                                    <i class="las la-{{ $task->status == 1 ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>

                                            <!-- Run Immediately -->
                                            <form action="{{ route('admin.bot.notifications.run.schedule', $task->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" title="Run Immediately Now" onclick="return confirm('Execute this scheduled task right now?')">
                                                    <i class="las la-paper-plane"></i>
                                                </button>
                                            </form>

                                            <!-- Delete -->
                                            <button type="button" class="btn btn-outline-danger btnDeleteSchedule" data-id="{{ $task->id }}" data-title="{{ $task->title }}" title="Delete Task">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="las la-calendar-times text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No scheduled notification tasks found</h6>
                                        <p class="text-muted small">Create your first automated reminder or broadcast above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($tasks->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($tasks) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Create Scheduled Task -->
<div class="modal fade" id="createScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-plus-circle me-1"></i> New Scheduled Notification Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bot.notifications.store.schedule') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row gy-3">
                        
                        <!-- Title -->
                        <div class="col-md-7">
                            <label class="fw-bold mb-1">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Daily Payment Reminder / Weekly System Update" required>
                        </div>

                        <!-- Event Type -->
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">Event Category <span class="text-danger">*</span></label>
                            <select name="event_type" class="form-select" required>
                                <option value="reminder">Reminder</option>
                                <option value="update">Product / System Update</option>
                                <option value="broadcast">Promotional Broadcast</option>
                                <option value="custom">Custom Notification</option>
                            </select>
                        </div>

                        <!-- Target Selector Type -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Target Audience <span class="text-danger">*</span></label>
                            <select name="target_type" id="targetTypeSelect" class="form-select" required>
                                <option value="single">Single WhatsApp Phone Number</option>
                                <option value="contact_list">Contact List</option>
                                <option value="group">WhatsApp Group</option>
                                <option value="all_contacts">All Saved Contacts</option>
                            </select>
                        </div>

                        <!-- Dynamic Target Identifier Input -->
                        <div class="col-md-6" id="targetIdentifierWrapper">
                            <label class="fw-bold mb-1" id="targetIdentifierLabel">Target Phone Number</label>
                            
                            <!-- Input for Single Phone -->
                            <input type="text" name="target_identifier" id="targetInputSingle" class="form-control" placeholder="e.g. 923216793596">

                            <!-- Select for Contact List -->
                            <select id="targetSelectContactList" class="form-select d-none">
                                <option value="">-- Choose Contact List --</option>
                                @foreach($contactLists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->contacts_count ?? 0 }} members)</option>
                                @endforeach
                            </select>

                            <!-- Select for WhatsApp Group -->
                            <select id="targetSelectGroup" class="form-select d-none">
                                <option value="">-- Choose WhatsApp Group --</option>
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->group_id }}">{{ $grp->group_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Recurrence Type -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Schedule Frequency <span class="text-danger">*</span></label>
                            <select name="schedule_type" id="scheduleTypeSelect" class="form-select" required>
                                <option value="once">Once (Specific Date & Time)</option>
                                <option value="daily">Daily Recurring</option>
                                <option value="weekly">Weekly Recurring</option>
                                <option value="monthly">Monthly Recurring</option>
                            </select>
                        </div>

                        <!-- WhatsApp Sender Account -->
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">WhatsApp Sender Account</label>
                            <select name="session_id" class="form-select">
                                <option value="">Primary Active Account</option>
                                @foreach($connectedAccounts as $acc)
                                    <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number ?? $acc->session_id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Schedule Timing Fields -->
                        <div class="col-12" id="timingContainer">
                            <!-- Once: DateTime -->
                            <div class="row" id="timeOnceGroup">
                                <div class="col-md-12">
                                    <label class="fw-bold mb-1">Execution Date & Time</label>
                                    <input type="datetime-local" name="schedule_datetime" class="form-control" value="{{ now()->addMinutes(10)->format('Y-m-d\TH:i') }}">
                                </div>
                            </div>

                            <!-- Recurring Time -->
                            <div class="row d-none" id="timeRecurringGroup">
                                <div class="col-md-4" id="dayOfWeekCol">
                                    <label class="fw-bold mb-1">Day of Week</label>
                                    <select name="schedule_day_of_week" class="form-select">
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                        <option value="Sunday">Sunday</option>
                                    </select>
                                </div>

                                <div class="col-md-4 d-none" id="dayOfMonthCol">
                                    <label class="fw-bold mb-1">Day of Month</label>
                                    <input type="number" name="schedule_day_of_month" class="form-control" min="1" max="31" value="1">
                                </div>

                                <div class="col-md-4" id="timeOfDayCol">
                                    <label class="fw-bold mb-1">Time of Day (24h)</label>
                                    <input type="time" name="schedule_time" class="form-control" value="09:00">
                                </div>
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="col-12">
                            <label class="fw-bold mb-1">Notification Message <span class="text-danger">*</span></label>
                            <textarea name="message" rows="4" class="form-control" placeholder="Write your notification or reminder message here..." required></textarea>
                            <small class="text-muted">Supported tags: <code>@{{name}}</code>, <code>@{{phone}}</code></small>
                        </div>

                        <!-- Media Attachment (Optional) -->
                        <div class="col-md-8">
                            <label class="fw-bold mb-1">Media Attachment URL (Optional)</label>
                            <input type="url" name="media_url" class="form-control" placeholder="https://example.com/banner.jpg">
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Media Type</label>
                            <select name="media_type" class="form-select">
                                <option value="text">None (Text Only)</option>
                                <option value="image">Image (JPG/PNG)</option>
                                <option value="video">Video (MP4)</option>
                                <option value="document">Document (PDF/DOC)</option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--primary px-4"><i class="las la-save me-1"></i> Save Task Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Delete Confirmation -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-trash me-1"></i> Delete Task</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteScheduleForm" method="POST">
                @csrf
                <div class="modal-body text-center p-4">
                    <p class="mb-0">Are you sure you want to delete task <strong id="deleteTaskTitle"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        // Dynamic Target Switcher
        $('#targetTypeSelect').on('change', function () {
            var val = $(this).val();
            var $single = $('#targetInputSingle');
            var $list = $('#targetSelectContactList');
            var $group = $('#targetSelectGroup');
            var $label = $('#targetIdentifierLabel');

            $single.addClass('d-none').removeAttr('name');
            $list.addClass('d-none').removeAttr('name');
            $group.addClass('d-none').removeAttr('name');

            if (val === 'single') {
                $label.text('Target Phone Number');
                $single.removeClass('d-none').attr('name', 'target_identifier');
            } else if (val === 'contact_list') {
                $label.text('Choose Contact List');
                $list.removeClass('d-none').attr('name', 'target_identifier');
            } else if (val === 'group') {
                $label.text('Choose WhatsApp Group');
                $group.removeClass('d-none').attr('name', 'target_identifier');
            } else {
                $label.text('All Contacts');
            }
        });

        // Dynamic Schedule Type Switcher
        $('#scheduleTypeSelect').on('change', function () {
            var val = $(this).val();
            var $once = $('#timeOnceGroup');
            var $rec = $('#timeRecurringGroup');
            var $dayWeek = $('#dayOfWeekCol');
            var $dayMonth = $('#dayOfMonthCol');

            if (val === 'once') {
                $once.removeClass('d-none');
                $rec.addClass('d-none');
            } else {
                $once.addClass('d-none');
                $rec.removeClass('d-none');

                if (val === 'daily') {
                    $dayWeek.addClass('d-none');
                    $dayMonth.addClass('d-none');
                } else if (val === 'weekly') {
                    $dayWeek.removeClass('d-none');
                    $dayMonth.addClass('d-none');
                } else if (val === 'monthly') {
                    $dayWeek.addClass('d-none');
                    $dayMonth.removeClass('d-none');
                }
            }
        });

        // Delete Task Modal
        $('.btnDeleteSchedule').on('click', function () {
            var id = $(this).data('id');
            var title = $(this).data('title');
            var url = "{{ route('admin.bot.notifications.delete.schedule', ':id') }}".replace(':id', id);
            $('#deleteTaskTitle').text(title);
            $('#deleteScheduleForm').attr('action', url);
            $('#deleteScheduleModal').modal('show');
        });

    })(jQuery);
</script>
@endpush
