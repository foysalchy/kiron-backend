<?php

namespace App\Enums;

enum Status: int
{
  case Inactive   = 0;
  case Active     = 1;
  case Pending    = 2;
  case Approved   = 3;
  case Confirmed  = 4;
  case Processing = 5;
  case Shipped    = 6;
  case Delivered  = 7;
  case Completed  = 8;
  case Paid       = 9;
  case Cancelled  = 10;
  case Returned   = 11;
  case Suspended  = 12;
  case Draft      = 13;
  case Trashed    = 14;
  case Hold       = 15;
  case Waiting    = 16;
  case Cleared    = 17;
  case NotCleared = 18;
  case Replied    = 19;
  case Closed     = 20;
  case Sent       = 21;
  case Accepted   = 22;
  case Rejected   = 23;
  case Expired    = 24;

  public function label(): string
  {
    return match ($this) {
      self::Inactive   => 'Inactive',
      self::Active     => 'Active',
      self::Pending    => 'Pending',
      self::Approved   => 'Approved',
      self::Confirmed  => 'Confirmed',
      self::Processing => 'Processing',
      self::Shipped    => 'Shipped',
      self::Delivered  => 'Delivered',
      self::Completed  => 'Completed',
      self::Paid       => 'Paid',
      self::Cancelled  => 'Cancelled',
      self::Returned   => 'Returned',
      self::Suspended  => 'Suspended',
      self::Draft      => 'Draft',
      self::Trashed    => 'Deleted',
      self::Hold       => 'Hold',
      self::Waiting    => 'Waiting',
      self::Cleared    => 'Cleared',
      self::NotCleared => 'NotCleared',
      self::Replied    => 'Replied',
      self::Closed     => 'Closed',
      self::Sent       => 'Sent',
      self::Accepted   => 'Accepted',
      self::Rejected   => 'Rejected',
      self::Expired    => 'Expired',
    };
  }

  public function slug(): string
  {
    return strtolower($this->name);
  }
}
