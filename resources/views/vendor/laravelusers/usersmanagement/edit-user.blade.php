@extends(config('laravelusers.laravelUsersBladeExtended'))

@section('template_title')
    {!! trans('laravelusers::laravelusers.editing-user', ['name' => $user->name]) !!}
@endsection

@section('template_linked_css')
    @if(config('laravelusers.enabledDatatablesJs'))
        <link rel="stylesheet" type="text/css" href="{{ config('laravelusers.datatablesCssCDN') }}">
    @endif
    @if(config('laravelusers.fontAwesomeEnabled'))
        <link rel="stylesheet" type="text/css" href="{{ config('laravelusers.fontAwesomeCdn') }}">
    @endif
    @include('laravelusers::partials.styles')
    @include('laravelusers::partials.bs-visibility-css')
@endsection

@section('content')
    <div class="container">
        @if(config('laravelusers.enablePackageBootstapAlerts'))
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    @include('laravelusers::partials.form-status')
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            {!! trans('laravelusers::laravelusers.editing-user', ['name' => $user->name]) !!}
                            <div class="pull-right">
                                <a href="{{ route('users') }}" class="btn btn-light btn-sm float-right"
                                   data-toggle="tooltip" data-placement="top"
                                   title="{!! trans('laravelusers::laravelusers.tooltips.back-users') !!}">
                                    @if(config('laravelusers.fontAwesomeEnabled'))
                                        <i class="fas fa-fw fa-reply-all" aria-hidden="true"></i>
                                    @endif
                                    {!! trans('laravelusers::laravelusers.buttons.back-to-users') !!}
                                </a>
                                <a href="{{ url('/users/' . $user->id) }}" class="btn btn-light btn-sm float-right"
                                   data-toggle="tooltip" data-placement="left"
                                   title="{!! trans('laravelusers::laravelusers.tooltips.back-user') !!}">
                                    @if(config('laravelusers.fontAwesomeEnabled'))
                                        <i class="fas fa-fw fa-reply" aria-hidden="true"></i>
                                    @endif
                                    {!! trans('laravelusers::laravelusers.buttons.back-to-user') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! Form::open(array('route' => ['users.update', $user->id], 'method' => 'PUT', 'role' => 'form', 'class' => 'needs-validation')) !!}
                        {!! csrf_field() !!}
                        <div class="form-group has-feedback row {{ $errors->has('name') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('name', trans('laravelusers::forms.create_user_label_username'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('name', $user->name, array('id' => 'name', 'class' => 'form-control', 'placeholder' => trans('laravelusers::forms.create_user_ph_username'))) !!}
                                    <div class="input-group-append">
                                        <label class="input-group-text" for="name">
                                            @if(config('laravelusers.fontAwesomeEnabled'))
                                                <i class="fa fa-fw {!! trans('laravelusers::forms.create_user_icon_username') !!}"
                                                   aria-hidden="true"></i>
                                            @else
                                                {!! trans('laravelusers::forms.create_user_label_username') !!}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group has-feedback row {{ $errors->has('email') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('email', trans('laravelusers::forms.create_user_label_email'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('email', $user->email, array('id' => 'email', 'class' => 'form-control', 'placeholder' => trans('laravelusers::forms.create_user_ph_email'))) !!}
                                    <div class="input-group-append">
                                        <label for="email" class="input-group-text">
                                            @if(config('laravelusers.fontAwesomeEnabled'))
                                                <i class="fa fa-fw {!! trans('laravelusers::forms.create_user_icon_email') !!}"
                                                   aria-hidden="true"></i>
                                            @else
                                                {!! trans('laravelusers::forms.create_user_label_email') !!}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                @endif
                            </div>
                        </div>
                        <div
                            class="form-group has-feedback row {{ $errors->has('contributionGroup') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('contributionGroup', trans('laravelusers::laravelusers.show-user.contributionGroup'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    <select class="custom-select form-control" name="contributionGroup" id="contributionGroup">
                                        <option
                                            value="">{!! trans('laravelusers::laravelusers.show-user.contributionGroup') !!}</option>
                                        @foreach(App\Enums\EnumContributionGroup::translated() as $key => $translated)
                                            @if ($user->contributionGroup)
                                                <option
                                                    value="{{ $key }}" {{  $key == $user->contributionGroup ? 'selected="selected"' : '' }}>{{ $translated }}</option>
                                            @else
                                                <option value="{{ $key }}">{{ $translated }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <label class="input-group-text" for="contributionGroup">
                                            @if(config('laravelusers.fontAwesomeEnabled'))
                                                <i class="fa fa-fw" aria-hidden="true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                                        <path
                                                            d="M224 256c70.7 0 128-57.31 128-128S294.7 0 224 0C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3c-95.73 0-173.3 77.6-173.3 173.3C0 496.5 15.52 512 34.66 512H413.3C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304zM479.1 320h-73.85C451.2 357.7 480 414.1 480 477.3C480 490.1 476.2 501.9 470 512h138C625.7 512 640 497.6 640 479.1C640 391.6 568.4 320 479.1 320zM432 256C493.9 256 544 205.9 544 144S493.9 32 432 32c-25.11 0-48.04 8.555-66.72 22.51C376.8 76.63 384 101.4 384 128c0 35.52-11.93 68.14-31.59 94.71C372.7 243.2 400.8 256 432 256z"/>
                                                    </svg>
                                                </i>
                                            @else
                                                {!! trans('laravelusers::laravelusers.show-user.contributionGroup') !!}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('contributionGroup'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('contributionGroup') }}</strong>
                                        </span>
                                @endif
                            </div>
                        </div>
                        <div
                            class="form-group has-feedback row {{ $errors->has('countShares') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('countShares', trans('laravelusers::laravelusers.show-user.countShares'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    <select class="custom-select form-control" name="countShares"
                                            id="countShares">
                                        <option
                                            value="">{!! trans('laravelusers::laravelusers.show-user.countShares') !!}</option>
                                        @foreach(range(1,5) as $count)
                                            @if ($user->countShares)
                                                <option
                                                    value="{{ $count }}" {{  $count == $user->countShares ? 'selected="selected"' : '' }}>{{ $count }}</option>
                                            @else
                                                <option value="{{ $count }}">{{ $count }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <label class="input-group-text" for="countShares">
                                            @if(config('laravelusers.fontAwesomeEnabled'))
                                                <i class="fa fa-fw" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M416 127.1h-58.23l9.789-58.74c2.906-17.44-8.875-33.92-26.3-36.83c-17.53-2.875-33.92 8.891-36.83 26.3L292.9 127.1H197.8l9.789-58.74c2.906-17.44-8.875-33.92-26.3-36.83c-17.53-2.875-33.92 8.891-36.83 26.3L132.9 127.1H64c-17.67 0-32 14.33-32 32C32 177.7 46.33 191.1 64 191.1h58.23l-21.33 128H32c-17.67 0-32 14.33-32 32c0 17.67 14.33 31.1 32 31.1h58.23l-9.789 58.74c-2.906 17.44 8.875 33.92 26.3 36.83C108.5 479.9 110.3 480 112 480c15.36 0 28.92-11.09 31.53-26.73l11.54-69.27h95.12l-9.789 58.74c-2.906 17.44 8.875 33.92 26.3 36.83C268.5 479.9 270.3 480 272 480c15.36 0 28.92-11.09 31.53-26.73l11.54-69.27H384c17.67 0 32-14.33 32-31.1c0-17.67-14.33-32-32-32h-58.23l21.33-128H416c17.67 0 32-14.32 32-31.1C448 142.3 433.7 127.1 416 127.1zM260.9 319.1H165.8L187.1 191.1h95.12L260.9 319.1z"/></svg></i>
                                            @else
                                                {!! trans('laravelusers::laravelusers.show-user.countShares') !!}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('countShares'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('countShares') }}</strong>
                                        </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group has-feedback row {{ $errors->has('joinDate') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('joinDate', trans('laravelusers::laravelusers.show-user.joinDate'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('joinDate', isset($user->joinDate) ? $user->joinDate->format(config('app.date_format')) : null, array('id' => 'contributionGroup', 'class' => 'form-control', 'placeholder' => \Carbon\Carbon::now()->format(config('app.date_format')))) !!}
                                    <div class="input-group-append">
                                        <label class="input-group-text" for="joinDate">
                                            @if(config('laravelusers.fontAwesomeEnabled'))
                                                <i class="fa fa-fw {!! trans('laravelusers::laravelusers.show-user.joinDate') !!}"
                                                   aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 32V64H288V32C288 14.33 302.3 0 320 0C337.7 0 352 14.33 352 32V64H400C426.5 64 448 85.49 448 112V160H0V112C0 85.49 21.49 64 48 64H96V32C96 14.33 110.3 0 128 0C145.7 0 160 14.33 160 32zM0 192H448V464C448 490.5 426.5 512 400 512H48C21.49 512 0 490.5 0 464V192zM80 256C71.16 256 64 263.2 64 272V368C64 376.8 71.16 384 80 384H176C184.8 384 192 376.8 192 368V272C192 263.2 184.8 256 176 256H80z"/></svg></i>
                                            @else
                                                {!! trans('laravelusers::laravelusers.show-user.joinDate') !!}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('joinDate'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('joinDate') }}</strong>
                                        </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group has-feedback row {{ $errors->has('exitDate') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('exitDate', trans('laravelusers::laravelusers.show-user.exitDate'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('exitDate', isset($user->exitDate) ? $user->exitDate->format(config('app.date_format')) : null, array('id' => 'contributionGroup', 'class' => 'form-control', 'placeholder' => trans('laravelusers::laravelusers.show-user.exitDate'))) !!}
                                    <div class="input-group-append">
                                        <label class="input-group-text" for="exitDate">
                                            @if(config('laravelusers.fontAwesomeEnabled'))
                                                <i class="fa fa-fw {!! trans('laravelusers::laravelusers.show-user.exitDate') !!}"
                                                   aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 32V64H288V32C288 14.33 302.3 0 320 0C337.7 0 352 14.33 352 32V64H400C426.5 64 448 85.49 448 112V160H0V112C0 85.49 21.49 64 48 64H96V32C96 14.33 110.3 0 128 0C145.7 0 160 14.33 160 32zM0 192H448V464C448 490.5 426.5 512 400 512H48C21.49 512 0 490.5 0 464V192zM80 256C71.16 256 64 263.2 64 272V368C64 376.8 71.16 384 80 384H176C184.8 384 192 376.8 192 368V272C192 263.2 184.8 256 176 256H80z"/></svg></i>
                                            @else
                                                {!! trans('laravelusers::laravelusers.show-user.exitDate') !!}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('exitDate'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('exitDate') }}</strong>
                                        </span>
                                @endif
                            </div>
                        </div>
                        @if($rolesEnabled)
                            <div class="form-group has-feedback row {{ $errors->has('role') ? ' has-error ' : '' }}">
                                @if(config('laravelusers.fontAwesomeEnabled'))
                                    {!! Form::label('role', trans('laravelusers::forms.create_user_label_role'), array('class' => 'col-md-3 control-label')); !!}
                                @endif
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <select class="custom-select form-control" name="role[]" id="role" multiple>
                                            <option
                                                value="">{!! trans('laravelusers::forms.create_user_ph_role') !!}</option>
                                            @if ($roles)
                                                @foreach($roles as $role)
                                                    @if ($currentRole)
                                                        <option
                                                            value="{{ $role->id }}" {{ in_array($role->id, $currentRole) ? 'selected="selected"' : '' }}>{{ $role->name }}</option>
                                                    @else
                                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="input-group-append">
                                            <label class="input-group-text" for="role">
                                                @if(config('laravelusers.fontAwesomeEnabled'))
                                                    <i class="{!! trans('laravelusers::forms.create_user_icon_role') !!}"
                                                       aria-hidden="true"></i>
                                                @else
                                                    {!! trans('laravelusers::forms.create_user_label_username') !!}
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                    @if ($errors->has('role'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('role') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="pw-change-container">
                            <div
                                class="form-group has-feedback row {{ $errors->has('password') ? ' has-error ' : '' }}">
                                @if(config('laravelusers.fontAwesomeEnabled'))
                                    {!! Form::label('password', trans('laravelusers::forms.create_user_label_password'), array('class' => 'col-md-3 control-label')); !!}
                                @endif
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {!! Form::password('password', array('id' => 'password', 'class' => 'form-control ', 'placeholder' => trans('laravelusers::forms.create_user_ph_password'))) !!}
                                        <div class="input-group-append">
                                            <label class="input-group-text" for="password">
                                                @if(config('laravelusers.fontAwesomeEnabled'))
                                                    <i class="fa fa-fw {!! trans('laravelusers::forms.create_user_icon_password') !!}"
                                                       aria-hidden="true"></i>
                                                @else
                                                    {!! trans('laravelusers::forms.create_user_label_password') !!}
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                    @endif
                                </div>
                            </div>
                            <div
                                class="form-group has-feedback row {{ $errors->has('password_confirmation') ? ' has-error ' : '' }}">
                                @if(config('laravelusers.fontAwesomeEnabled'))
                                    {!! Form::label('password_confirmation', trans('laravelusers::forms.create_user_label_pw_confirmation'), array('class' => 'col-md-3 control-label')); !!}
                                @endif
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {!! Form::password('password_confirmation', array('id' => 'password_confirmation', 'class' => 'form-control', 'placeholder' => trans('laravelusers::forms.create_user_ph_pw_confirmation'))) !!}
                                        <div class="input-group-append">
                                            <label class="input-group-text" for="password_confirmation">
                                                @if(config('laravelusers.fontAwesomeEnabled'))
                                                    <i class="fa fa-fw {!! trans('laravelusers::forms.create_user_icon_pw_confirmation') !!}"
                                                       aria-hidden="true"></i>
                                                @else
                                                    {!! trans('laravelusers::forms.create_user_label_pw_confirmation') !!}
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-2">
                                <a href="#" class="btn btn-outline-secondary btn-block btn-change-pw mt-3"
                                   title="{!! trans('laravelusers::forms.change-pw') !!}">
                                    <i class="fa fa-fw fa-lock" aria-hidden="true"></i>
                                    <span></span> {!! trans('laravelusers::forms.change-pw') !!}
                                </a>
                            </div>
                            <div class="col-12 col-sm-6">
                                {!! Form::button(trans('laravelusers::forms.save-changes'), array('class' => 'btn btn-success btn-block margin-bottom-1 mt-3 mb-2 btn-save','type' => 'button', 'data-toggle' => 'modal', 'data-target' => '#confirmSave', 'data-title' => trans('laravelusers::modals.edit_user__modal_text_confirm_title'), 'data-message' => trans('laravelusers::modals.edit_user__modal_text_confirm_message'))) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('laravelusers::modals.modal-save')
    @include('laravelusers::modals.modal-delete')

@endsection

@section('template_scripts')
    @include('laravelusers::scripts.delete-modal-script')
    @include('laravelusers::scripts.save-modal-script')
    @include('laravelusers::scripts.check-changed')
    @if(config('laravelusers.tooltipsEnabled'))
        @include('laravelusers::scripts.tooltips')
    @endif
@endsection

