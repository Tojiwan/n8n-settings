{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-dropdown title="AI Chatbot" icon="las la-robot">
    <x-backpack::menu-dropdown-item title="Workflows" icon="la la-gear" :link="backpack_url('')" />
    <x-backpack::menu-dropdown-item title="System Configuration" icon="la la-gear" :link="backpack_url('workflow-form')" />
</x-backpack::menu-dropdown>