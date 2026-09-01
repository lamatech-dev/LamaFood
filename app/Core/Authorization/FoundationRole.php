<?php

namespace App\Core\Authorization;

enum FoundationRole: string
{
    case LamatechSuperAdmin = 'lamatech-super-admin';
    case BusinessOwner = 'business-owner';
    case ContentEditor = 'content-editor';
}
