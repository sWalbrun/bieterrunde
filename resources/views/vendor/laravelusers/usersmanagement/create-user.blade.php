@extends(config('laravelusers.laravelUsersBladeExtended'))

@section('template_title')
    {!! trans('laravelusers::laravelusers.create-new-user') !!}
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
                            {!! trans('laravelusers::laravelusers.create-new-user') !!}
                            <div class="pull-right">
                                <a href="{{ route('users') }}" class="btn btn-light btn-sm float-right"
                                   data-toggle="tooltip" data-placement="left"
                                   title="{!! trans('laravelusers::laravelusers.tooltips.back-users') !!}">
                                    @if(config('laravelusers.fontAwesomeEnabled'))
                                        <i class="fas fa-fw fa-reply-all" aria-hidden="true"></i>
                                    @endif
                                    {!! trans('laravelusers::laravelusers.buttons.back-to-users') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! Form::open(array('route' => 'users.store', 'method' => 'POST', 'role' => 'form', 'class' => 'needs-validation')) !!}
                        {!! csrf_field() !!}
                        <div class="form-group has-feedback row {{ $errors->has('email') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('email', trans('laravelusers::forms.create_user_label_email'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('email', NULL, array('id' => 'email', 'class' => 'form-control', 'placeholder' => trans('laravelusers::forms.create_user_ph_email'))) !!}
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
                        <div class="form-group has-feedback row {{ $errors->has('contributionGroup') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('name', trans('laravelusers::forms.create_user_label_username'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('name', NULL, array('id' => 'contributionGroup', 'class' => 'form-control', 'placeholder' => trans('laravelusers::forms.create_user_ph_username'))) !!}
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
                        <div
                            class="form-group has-feedback row {{ $errors->has('contributionGroup') ? ' has-error ' : '' }}">
                            @if(config('laravelusers.fontAwesomeEnabled'))
                                {!! Form::label('contributionGroup', trans('laravelusers::laravelusers.show-user.contributionGroup'), array('class' => 'col-md-3 control-label')); !!}
                            @endif
                            <div class="col-md-9">
                                <div class="input-group">
                                    <select class="custom-select form-control" name="contributionGroup"
                                            id="contributionGroup">
                                        <option
                                            value="">{!! trans('laravelusers::laravelusers.show-user.contributionGroup') !!}</option>
                                        @foreach(App\Enums\EnumContributionGroup::translated() as $key => $translated)
                                            <option value="{{ $key }}">{{ $translated }}</option>
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
                        @if($rolesEnabled)
                            <div class="form-group has-feedback row {{ $errors->has('role') ? ' has-error ' : '' }}">
                                @if(config('laravelusers.fontAwesomeEnabled'))
                                    {!! Form::label('role', trans('laravelusers::forms.create_user_label_role'), array('class' => 'col-md-3 control-label')); !!}
                                @endif
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <select class="custom-select form-control" name="role" id="role">
                                            <option
                                                value="">{!! trans('laravelusers::forms.create_user_ph_role') !!}</option>
                                            @if ($roles)
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
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
                        <div class="form-group has-feedback row {{ $errors->has('password') ? ' has-error ' : '' }}">
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
                        {!! Form::button(trans('laravelusers::forms.create_user_button_text'), array('class' => 'btn btn-success margin-bottom-1 mb-1 float-right','type' => 'submit' )) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('template_scripts')
    @if(config('laravelusers.tooltipsEnabled'))
        @include('laravelusers::scripts.tooltips')
    @endif
@endsection
