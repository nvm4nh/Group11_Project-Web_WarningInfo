<?php
/* Nhãn + màu dùng chung cho các trang cán bộ (TV4 – Milestone 3) */
$priorityBadge = [
  'critical' => ['Nghiêm trọng', 'bg-danger'],
  'high'     => ['Cao',          'bg-orange'],
  'medium'   => ['Trung bình',   'bg-warning text-dark'],
  'low'      => ['Thấp',         'bg-success'],
];
$statusBadge = [
  'new'            => 'bg-secondary',
  'received'       => 'bg-secondary',
  'need_more_info' => 'bg-warning text-dark',
  'verifying'      => 'bg-info text-dark',
  'verified'       => 'bg-info text-dark',
  'assigned'       => 'bg-primary',
  'in_progress'    => 'bg-primary',
  'resolved'       => 'bg-success',
  'closed'         => 'bg-dark',
  'rejected'       => 'bg-dark',
];
$assignmentBadge = [
  'assigned'    => ['Chờ nhận việc',        'bg-warning text-dark'],
  'accepted'    => ['Đã nhận việc',         'bg-info text-dark'],
  'in_progress' => ['Đang xử lý',           'bg-primary'],
  'completed'   => ['Đã hoàn thành',        'bg-success'],
  'cancelled'   => ['Đã chuyển người khác', 'bg-secondary'],
];
