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
  case Transferred = 25;
  case Disposed    = 26;
  case ReadyToShipped = 27;
  case HandovertoCourier = 28;
  case InTransit = 29;
  case ReturntoCourier = 30;
  case ReturnReceived = 31;
  case ReturnRequest = 32;
  case Solved = 33;
  case WaitForResponse         = 34;
  case WaitingForClientResponse = 35;
  case Open = 36;
  case Resumed = 37;


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
      self::Transferred => 'Transferred',
      self::Disposed   => 'Disposed',
      self::ReadyToShipped => 'Ready to Shipped',
      self::HandovertoCourier => 'Handover to Courier',
      self::InTransit => 'In Transit',
      self::ReturntoCourier => 'Return to Courier',
      self::ReturnReceived => 'Return Received',
      self::ReturnRequest => 'Return Request',
      self::Solved => 'Solved',
      self::WaitForResponse => 'Waiting for Response',
      self::WaitingForClientResponse => 'Waiting for Client Response',
      self::Open => 'Open',
      self::Resumed => 'Resumed',
    };
  }
  public const ORDER_FLOW = [
    self::Draft,
    self::Pending,
    self::Processing,
    self::Confirmed,
    self::ReadyToShipped,
    self::Shipped,
    self::HandovertoCourier,
    self::InTransit,
    self::Delivered,
    self::ReturntoCourier,
    self::ReturnReceived,
    self::Cancelled,

  ];

  public static function getOrderProgress(int $currentStatus): array
  {
    $flow = self::ORDER_FLOW;

    $result = [];

    foreach ($flow as $status) {
      $result[] = [
        'status' => $status,
        'is_completed' => $status->value <= $currentStatus,
        'is_current' => $status->value === $currentStatus,
      ];
    }

    return $result;
  }
  public function slug(): string
  {
    return strtolower($this->name);
  }
}
