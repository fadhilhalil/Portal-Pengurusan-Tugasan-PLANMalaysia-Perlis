import React, { useState, useEffect } from 'react';
import { 
  LayoutDashboard, 
  Users, 
  Briefcase, 
  CheckSquare, 
  LogOut, 
  Bell, 
  Clock, 
  FileText,
  Plus,
  Calendar as CalendarIcon,
  ChevronRight,
  Menu,
  X,
  Pencil,
  Trash2,
  Search,
  Home,
  Shield,
  ExternalLink,
  Info,
  Phone,
  Mail,
  MapPin,
  Globe,
  CheckCircle2,
  AlertCircle,
  UserCheck,
  UserCircle,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { api } from '@/lib/api';
import { 
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { User, Project, Task, Role, ProjectStatus } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'sonner';
import { format, differenceInDays, isBefore } from 'date-fns';
import { 
  BarChart, 
  Bar, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell
} from 'recharts';

import logoPMPerlis from '@/components/ui/PMPERLIS_New.png';

const MSIA_TIME_ZONE = 'Asia/Kuala_Lumpur';

const DAY_NAMES_MS = [
  'Ahad',
  'Isnin',
  'Selasa',
  'Rabu',
  'Khamis',
  'Jumaat',
  'Sabtu',
];

const MONTH_NAMES_MS = [
  'Januari',
  'Februari',
  'Mac',
  'April',
  'Mei',
  'Jun',
  'Julai',
  'Ogos',
  'September',
  'Oktober',
  'November',
  'Disember',
];

function getMalaysiaDateParts(date = new Date()) {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: MSIA_TIME_ZONE,
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  }).formatToParts(date);

  const get = (type: string) =>
    parts.find((part) => part.type === type)?.value ?? '';

  const day = Number(get('day'));
  const month = Number(get('month'));
  const year = Number(get('year'));
  const hour = Number(get('hour'));
  const minute = Number(get('minute'));
  const second = Number(get('second'));

  const malaysiaDate = new Date(
    Date.UTC(
      year,
      month - 1,
      day,
      hour,
      minute,
      second
    )
  );

  return {
    day,
    month,
    year,
    hour,
    minute,
    second,
    weekday:
      DAY_NAMES_MS[malaysiaDate.getUTCDay()],
  };
}

function formatMalaysiaDate(
  date = new Date()
) {
  const parts =
    getMalaysiaDateParts(date);

  return `${parts.weekday}, ${parts.day} ${MONTH_NAMES_MS[parts.month - 1]} ${parts.year}`;
}

function formatMalaysiaTime(
  date = new Date()
) {
  const parts =
    getMalaysiaDateParts(date);

  return `${String(parts.hour).padStart(2, '0')}:${String(parts.minute).padStart(2, '0')}:${String(parts.second).padStart(2, '0')}`;
}

// --- Login Component ---

const LoginModal = ({ 
  isOpen, 
  onClose, 
  onLogin, 
  users 
}: { 
  isOpen: boolean; 
  onClose: () => void; 
  onLogin: (user: User) => void;
  users: User[];
}) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const user = users.find((u) => u.email === email && u.password === password);
    if (user) {
      onLogin(user);
      onClose();
      toast.success(`Selamat datang, ${user.name}!`);
    } else {
      toast.error('Emel atau kata laluan salah.');
    }
  };

  const quickLogin = (selectedUser: User) => {
    onLogin(selectedUser);
    onClose();
    toast.success(`Selamat datang, ${selectedUser.name}! (${selectedUser.role})`);
  };

  return (
    <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
      <DialogContent className="sm:max-w-[450px]">
        <DialogHeader className="text-center">
          <div className="flex justify-center items-center mb-3">
            <img src={logoPMPerlis} alt="Logo PLANMalaysia Perlis" className="h-24 sm:h-28 w-auto object-contain drop-shadow-sm" />
          </div>
          <DialogTitle className="text-xl font-bold text-[#0f172a]">Log Masuk Staf Portal</DialogTitle>
          <DialogDescription className="text-[12px] text-[#64748b]">
            Jabatan Perancangan Bandar dan Desa Negeri Perlis (PLANMalaysia Perlis)
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-4 py-2">
          <div className="space-y-2">
            <Label htmlFor="email" className="text-[11px] font-bold text-[#64748b] uppercase">Emel</Label>
            <Input 
              id="email" 
              type="email" 
              placeholder="contoh: admin@example.com" 
              className="border-[#e2e8f0]" 
              value={email} 
              onChange={(e) => setEmail(e.target.value)} 
              required 
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password" className="text-[11px] font-bold text-[#64748b] uppercase">Kata Laluan</Label>
            <Input 
              id="password" 
              type="password" 
              className="border-[#e2e8f0]" 
              value={password} 
              onChange={(e) => setPassword(e.target.value)} 
              required 
            />
          </div>
          <Button type="submit" className="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold h-10">
            Log Masuk
          </Button>
        </form>

        <div className="pt-3 border-t border-[#e2e8f0]">
          <p className="text-[11px] font-bold text-[#64748b] uppercase mb-2 text-center">Log Masuk Pantas (Akaun Demo):</p>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
            {users.map((u) => (
              <Button
                key={u.id}
                type="button"
                variant="outline"
                className="justify-start text-left text-[11px] h-auto py-2 border-[#e2e8f0] hover:bg-blue-50 hover:border-[#2563eb]"
                onClick={() => quickLogin(u)}
              >
                <div className="truncate">
                  <p className="font-bold text-[#0f172a] truncate">{u.name}</p>
                  <p className="text-[10px] text-[#2563eb] uppercase font-semibold">{u.role}</p>
                </div>
              </Button>
            ))}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

// --- Portal Public Home Page ---

const PortalHome = ({ 
  data, 
  onLoginClick, 
  onViewTask 
}: { 
  data: any; 
  onLoginClick: () => void;
  onViewTask: (tab: string) => void;
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');

  const filteredProjects = data.projects.filter((p: any) => {
    const matchesSearch = p.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
                          p.description.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = statusFilter === 'all' || p.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const totalProjects = data.projects.length;
  const completedProjects = data.projects.filter((p: any) => p.status === 'Selesai').length;
  const inProgressProjects = data.projects.filter((p: any) => p.status === 'Dalam Proses').length;
  const staffCount = data.users.filter((u: any) => u.role === 'Staff').length;

  return (
    <div className="space-y-8">
      {/* Portal Announcement Ticker */}
      <div className="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-center gap-3 text-[13px] text-amber-900 shadow-sm">
        <span className="bg-amber-500 text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded shrink-0">
          Pengumuman Portal
        </span>
       <p className="truncate font-medium flex-1">
  📢 Sila pastikan semua kemajuan tugasan jabatan dikemaskini dari semasa ke semasa.
</p>
        <Badge variant="outline" className="border-amber-300 text-amber-800 text-[10px] hidden md:inline-flex shrink-0">
          Jabatan Perancangan Bandar dan Desa
        </Badge>
      </div>

      {/* Hero Welcome Section */}
      <div className="relative rounded-2xl bg-gradient-to-r from-[#1e293b] via-[#1e3a8a] to-[#2563eb] text-white p-8 md:p-10 shadow-lg overflow-hidden">
        <div className="absolute top-0 right-0 -mt-10 -mr-10 w-80 h-80 bg-white/5 rounded-full blur-3xl pointer-events-none" />
        <div className="relative z-10 max-w-3xl space-y-4">
          <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1 rounded-full text-xs text-blue-200 border border-white/10">
            <Globe className="w-3.5 h-3.5" />
            <span>Portal Rasmi Sistem Pengurusan Tugasan Jabatan (SPTJ)</span>
          </div>
          <h1 className="text-2xl md:text-4xl font-extrabold tracking-tight leading-tight">
            Selamat Datang ke Portal Pengurusan Tugasan PLANMalaysia Perlis
          </h1>
          <p className="text-sm md:text-base text-blue-100/90 leading-relaxed">
            Sistem pengurusan portal rasmi bagi pemantauan, penyelarasan, dan pelaporan tugasan Jabatan Perancangan Bandar dan Desa Negeri Perlis secara berpusat.
          </p>
          <div className="flex flex-wrap items-center gap-3 pt-2">
            <Button 
              className="bg-white text-[#1e3a8a] hover:bg-blue-50 font-bold px-5"
              onClick={onLoginClick}
            >
              <UserCheck className="w-4 h-4 mr-2 text-[#2563eb]" /> Log Masuk Staf
            </Button>
            <Button 
              variant="outline" 
              className="bg-transparent text-white border-white/30 hover:bg-white/10"
              onClick={() => onViewTask('dashboard')}
            >
              <LayoutDashboard className="w-4 h-4 mr-2" /> Lihat Dashboard Utama
            </Button>
          </div>
        </div>

        {/* Quick KPI Bar */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-8 border-t border-white/10">
          <div>
            <p className="text-xs text-blue-200 font-medium">Jumlah Tugasan</p>
            <p className="text-2xl md:text-3xl font-extrabold mt-1">{totalProjects}</p>
          </div>
          <div>
            <p className="text-xs text-blue-200 font-medium">Tugasan Selesai</p>
            <p className="text-2xl md:text-3xl font-extrabold mt-1 text-emerald-300">{completedProjects}</p>
          </div>
          <div>
            <p className="text-xs text-blue-200 font-medium">Dalam Proses</p>
            <p className="text-2xl md:text-3xl font-extrabold mt-1 text-sky-300">{inProgressProjects}</p>
          </div>
          <div>
            <p className="text-xs text-blue-200 font-medium">Jumlah Staf</p>
            <p className="text-2xl md:text-3xl font-extrabold mt-1">{staffCount}</p>
          </div>
        </div>
      </div>

      {/* Main Task Directory Public View */}
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0] bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <CardTitle className="text-lg font-bold text-[#0f172a]">Senarai Tugasan Jabatan Terkini</CardTitle>
            <CardDescription className="text-[12px] text-[#64748b]">
              Paparan awam senarai tugasan rasmi Jabatan Perancangan Bandar dan Desa
            </CardDescription>
          </div>
          <div className="flex flex-col sm:flex-row items-center gap-3">
            <div className="relative w-full sm:w-64">
              <Search className="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#64748b]" />
              <Input 
                placeholder="Cari tugasan..." 
                className="pl-9 border-[#e2e8f0] bg-white text-xs h-9"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-full sm:w-44 border-[#e2e8f0] bg-white text-xs h-9">
                <SelectValue placeholder="Semua Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Semua Status</SelectItem>
                <SelectItem value="Dalam Perancangan">Dalam Perancangan</SelectItem>
                <SelectItem value="Dalam Proses">Dalam Proses</SelectItem>
                <SelectItem value="Belum Selesai">Belum Selesai</SelectItem>
                <SelectItem value="Selesai">Selesai</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow className="border-b border-[#e2e8f0]">
                <TableHead className="text-[11px] uppercase font-bold text-[#64748b] h-10 px-6">Nama Tugasan</TableHead>
                <TableHead className="text-[11px] uppercase font-bold text-[#64748b] h-10">Deskripsi</TableHead>
                <TableHead className="text-[11px] uppercase font-bold text-[#64748b] h-10">Staf Terlibat</TableHead>
                <TableHead className="text-[11px] uppercase font-bold text-[#64748b] h-10">Tarikh Mula</TableHead>
                <TableHead className="text-[11px] uppercase font-bold text-[#64748b] h-10">Tarikh Akhir</TableHead>
                <TableHead className="text-[11px] uppercase font-bold text-[#64748b] h-10 px-6">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredProjects.map((p: any) => (
                <TableRow key={p.id} className="border-b border-[#e2e8f0] hover:bg-slate-50/80 transition-colors">
                  <TableCell className="px-6 py-4 font-bold text-[13px] text-[#0f172a]">{p.name}</TableCell>
                  <TableCell className="text-[12px] text-[#64748b] max-w-xs truncate">{p.description}</TableCell>
                  <TableCell className="text-[12px] text-[#64748b]">
                    <div className="flex flex-wrap gap-1">
                      {p.staffIds?.map((sid: string) => (
                        <Badge key={sid} variant="outline" className="text-[10px] py-0 h-5 bg-white border-slate-200">
                          {data.users.find((u: any) => u.id === sid)?.name || sid}
                        </Badge>
                      )) || (data.users.find((u: any) => u.id === p.staffId)?.name || '-')}
                    </div>
                  </TableCell>
                  <TableCell className="text-[12px] text-[#64748b]">
                    {p.createdAt ? format(new Date(p.createdAt), 'dd MMM yyyy') : '-'}
                  </TableCell>
                  <TableCell className="text-[12px] text-[#64748b]">
                    {p.endDate ? format(new Date(p.endDate), 'dd MMM yyyy') : '-'}
                  </TableCell>
                  <TableCell className="px-6">
                    <span className={`status-pill ${
                      p.status === 'Selesai' ? 'status-done' : 
                      p.status === 'Dalam Proses' ? 'status-progress' : 
                      p.status === 'Belum Selesai' ? 'status-pending' : 'status-info'
                    }`}>
                      {p.status || 'Dalam Perancangan'}
                    </span>
                  </TableCell>
                </TableRow>
              ))}
              {filteredProjects.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-12 text-[#64748b] text-sm">
                    Tiada tugasan ditemui mengikut kriteria carian.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Department Staff Directory Grid Preview */}
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl p-6">
        <div className="flex items-center justify-between mb-6 border-b border-[#e2e8f0] pb-4">
          <div>
            <h3 className="text-lg font-bold text-[#0f172a]">Direktori Pegawai & Staf Jabatan</h3>
            <p className="text-[12px] text-[#64748b]">
              Senarai pegawai bertugas di Jabatan Perancangan Bandar dan Desa Negeri Perlis
            </p>
          </div>
          <Button variant="outline" size="sm" onClick={() => onViewTask('directory')} className="text-xs">
            Lihat Semua direktori <ChevronRight className="w-3.5 h-3.5 ml-1" />
          </Button>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          {data.users.map((u: any) => (
            <div 
              key={u.id}
              className="p-4 border border-[#e2e8f0] rounded-xl bg-white hover:border-[#2563eb]/40 hover:shadow-md transition-all flex flex-col items-center text-center space-y-3"
            >
              <Avatar className="h-14 w-14 border-2 border-blue-100 shadow-sm">
  <AvatarFallback className="bg-slate-100 text-slate-500">
    <UserCircle className="h-9 w-9" />
  </AvatarFallback>
</Avatar>
              <div className="w-full">
                <p className="font-bold text-sm text-[#0f172a] truncate">{u.name}</p>
                <p className="text-[11px] text-[#64748b] truncate">{u.email}</p>
                <Badge 
                  variant="secondary" 
                  className={`mt-2 text-[10px] uppercase font-bold ${
                    u.role === 'Pengarah' ? 'bg-amber-100 text-amber-800' :
                    u.role === 'Admin' ? 'bg-purple-100 text-purple-800' :
                    'bg-blue-100 text-blue-800'
                  }`}
                >
                  {u.role}
                </Badge>
              </div>
            </div>
          ))}
        </div>
      </Card>
    </div>
  );
};

// --- Staff Directory View ---

const StaffDirectory = ({ users }: { users: User[] }) => {
  return (
    <div className="space-y-6">
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold text-[#0f172a]">Direktori Pegawai & Staf Jabatan</CardTitle>
          <CardDescription className="text-[#64748b]">
            Maklumat perhubungan dan peranan pegawai Jabatan Perancangan Bandar dan Desa Negeri Perlis
          </CardDescription>
        </CardHeader>
        <CardContent className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {users.map((u) => (
              <Card key={u.id} className="border-[#e2e8f0] shadow-none hover:shadow-md transition-all rounded-xl overflow-hidden">
                <CardContent className="p-6 flex items-start space-x-4">
                  <Avatar className="h-16 w-16 border-2 border-[#2563eb]/20 shrink-0">
                    <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${u.name}`} />
                    <AvatarFallback>{u.name[0]}</AvatarFallback>
                  </Avatar>
                  <div className="flex-1 min-w-0 space-y-1">
                    <div className="flex items-center justify-between">
                      <p className="font-bold text-base text-[#0f172a] truncate">{u.name}</p>
                    </div>
                    <p className="text-xs text-[#64748b] flex items-center gap-1">
                      <Mail className="w-3.5 h-3.5 shrink-0 text-[#2563eb]" /> {u.email}
                    </p>
                    <p className="text-xs text-[#64748b] flex items-center gap-1">
                      <Phone className="w-3.5 h-3.5 shrink-0 text-[#2563eb]" /> 04-976 1234 (Ext {100 + parseInt(u.id.slice(-2) || '1')})
                    </p>
                    <div className="pt-2">
                      <Badge 
                        className={`text-[10px] font-bold uppercase ${
                          u.role === 'Pengarah' ? 'bg-amber-100 text-amber-800 hover:bg-amber-100' :
                          u.role === 'Admin' ? 'bg-purple-100 text-purple-800 hover:bg-purple-100' :
                          'bg-blue-100 text-blue-800 hover:bg-blue-100'
                        }`}
                      >
                        {u.role}
                      </Badge>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

// --- Dashboard Component ---

const Dashboard = ({ user, data }: { user: User, data: any }) => {
  const stats = [
    { 
  label: 'Jumlah Tugasan Utama',
  value: data.projects.length,
  icon: Briefcase,
  color: 'text-blue-600',
  trend: 'Rekod tugasan jabatan',
  trendColor: 'text-slate-400'
},
    { label: 'Tugasan Aktif', value: data.tasks.filter((t: any) => t.status !== 'Completed').length, icon: CheckSquare, color: 'text-blue-600', trend: '82% dalam jadual', trendColor: 'text-slate-400' },
    { label: 'Staf Bertugas', value: data.users.filter((u: any) => u.role === 'Staff').length, icon: Users, color: 'text-blue-600', trend: '5 Admin | 23 Staf', trendColor: 'text-slate-400' },
    { label: 'Tugasan Lewat', value: data.tasks.filter((t: any) => t.status !== 'Completed' && isBefore(new Date(t.deadline), new Date())).length, icon: Clock, color: 'text-red-600', trend: 'Peringatan dihantar', trendColor: 'text-red-500' },
  ];

  const chartData = [
    { name: 'Belum Selesai', value: data.projects.filter((p: any) => p.status === 'Belum Selesai').length },
    { name: 'Dalam Proses', value: data.projects.filter((p: any) => p.status === 'Dalam Proses').length },
    { name: 'Perancangan', value: data.projects.filter((p: any) => p.status === 'Dalam Perancangan' || !p.status).length },
    { name: 'Selesai', value: data.projects.filter((p: any) => p.status === 'Selesai').length },
  ];

  const COLORS = ['#ef4444', '#3b82f6', '#818cf8', '#10b981'];

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        {stats.map((stat, i) => (
          <Card key={i} className="border-[#e2e8f0] shadow-sm rounded-xl">
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-2">
                <p className="text-[13px] font-medium text-[#64748b]">{stat.label}</p>
                <stat.icon className={`w-5 h-5 ${stat.color}`} />
              </div>
              <p className={`text-2xl font-bold ${stat.label === 'Tugasan Lewat' ? 'text-[#ef4444]' : 'text-[#0f172a]'}`}>
                {stat.value}
              </p>
              <p className={`text-[11px] mt-1 font-medium ${stat.trendColor}`}>{stat.trend}</p>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <Card className="lg:col-span-3 border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden flex flex-col">
          <div className="px-5 py-4 border-b border-[#e2e8f0] font-bold text-[#0f172a] flex justify-between items-center bg-slate-50/50">
            <span>Senarai Tugasan Jabatan</span>
            <Badge variant="outline" className="text-xs bg-white border-slate-200">
              {data.projects.length} Tugasan Berdaftar
            </Badge>
          </div>
          <CardContent className="p-0">
            <Table>
              <TableHeader className="bg-[#f8fafc]">
                <TableRow className="hover:bg-transparent border-b border-[#e2e8f0]">
                  <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-5">Nama Tugasan</TableHead>
                  <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Staf Terlibat</TableHead>
                  <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Status</TableHead>
                  <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-5">Kemajuan Sub-Tugasan</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.projects.slice(0, 6).map((project: any) => {
                  const projectTasks = data.tasks.filter((t: any) => t.projectId === project.id);
                  const progress = projectTasks.length > 0 
                    ? Math.round((projectTasks.filter((t: any) => t.status === 'Completed').length / projectTasks.length) * 100) 
                    : 0;
                  
                  return (
                    <TableRow key={project.id} className="border-b border-[#e2e8f0] hover:bg-slate-50/50">
                      <TableCell className="px-5 py-4 font-bold text-[13px] text-[#0f172a]">{project.name}</TableCell>
                      <TableCell className="text-[13px] text-[#64748b]">
                        <div className="flex -space-x-2">
                          {(project.staffIds || [project.staffId]).slice(0, 3).map((sid: string) => (
                            <Avatar key={sid} className="h-6 w-6 border-2 border-white">
                              <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${data.users.find((u: any) => u.id === sid)?.name}`} />
                              <AvatarFallback>{data.users.find((u: any) => u.id === sid)?.name?.[0]}</AvatarFallback>
                            </Avatar>
                          ))}
                          {(project.staffIds || [project.staffId]).length > 3 && (
                            <div className="h-6 w-6 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-[#64748b]">
                              +{(project.staffIds || [project.staffId]).length - 3}
                            </div>
                          )}
                        </div>
                      </TableCell>
                      <TableCell>
                        <span className={`status-pill ${
                          project.status === 'Selesai' ? 'status-done' : 
                          project.status === 'Dalam Proses' ? 'status-progress' : 
                          project.status === 'Belum Selesai' ? 'status-pending' : 'status-info'
                        }`}>
                          {project.status || 'Dalam Perancangan'}
                        </span>
                      </TableCell>
                      <TableCell className="px-5">
                        <div className="flex flex-col gap-1">
                          <span className="text-[11px] font-semibold">{progress}%</span>
                          <div className="h-1.5 w-full bg-[#f1f5f9] rounded-full overflow-hidden">
                            <div 
                              className={`h-full transition-all duration-500 ${progress === 100 ? 'bg-[#10b981]' : 'bg-[#2563eb]'}`} 
                              style={{ width: `${progress}%` }} 
                            />
                          </div>
                        </div>
                      </TableCell>
                    </TableRow>
                  );
                })}
                {data.projects.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={4} className="text-center py-12 text-[#64748b]">Tiada data tugasan jabatan aktif.</TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <Card className="lg:col-span-1 border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden flex flex-col">
          <div className="px-5 py-4 border-b border-[#e2e8f0] font-bold text-[#0f172a] bg-slate-50/50">
            <span>Timeline Terdekat</span>
          </div>
          <CardContent className="p-0">
            <ScrollArea className="h-[400px]">
              {data.tasks
                .filter((t: any) => t.status !== 'Completed')
                .sort((a: any, b: any) => new Date(a.deadline).getTime() - new Date(b.deadline).getTime())
                .slice(0, 5)
                .map((task: any) => {
                 const daysLeft = differenceInDays(
  new Date(task.deadline),
  new Date()
);

let deadlineText = '';

if (daysLeft < 0) {
  deadlineText = 'Tamat tempoh';
} else if (daysLeft === 0) {
  deadlineText = 'Tamat hari ini';
} else if (daysLeft === 1) {
  deadlineText = 'Tamat esok';
} else {
  deadlineText = `Tamat dalam ${daysLeft} hari`;
};
                  const isUrgent = daysLeft <= 3;
                  return (
                    <div key={task.id} className="timeline-item">
                      <p className="text-[13px] font-bold text-[#0f172a]">{task.title}</p>
                      <p className="text-[11px] text-[#64748b]">Staf: {data.users.find((u: any) => u.id === task.assignedTo)?.name}</p>
                      <div className="flex flex-col gap-0.5 mt-1">
                        <p className="text-[10px] text-[#64748b]">Mula: {task.startDate ? format(new Date(task.startDate), 'dd MMM yyyy') : '-'}</p>
                        {isUrgent ? (
                          <p className="text-[11px] font-bold text-[#ef4444] flex items-center gap-1">
                            ⚠️ {daysLeft < 0 ? 'Tamat tempoh' : `Tamat dalam ${daysLeft} hari`}
                          </p>
                        ) : (
                          <p className="text-[11px] font-bold text-[#2563eb] flex items-center gap-1">
                            📅 {format(new Date(task.deadline), 'dd MMM yyyy')}
                          </p>
                        )}
                      </div>
                    </div>
                  );
                })}
              {data.tasks.filter((t: any) => t.status !== 'Completed').length === 0 && (
                <div className="p-10 text-center text-[#64748b] text-sm">Tiada tugasan terdekat.</div>
              )}
            </ScrollArea>
          </CardContent>
        </Card>
      </div>

      {/* Chart Section */}
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl p-6">
        <h3 className="font-bold text-[#0f172a] mb-4 text-base">Analisis Status Tugasan Jabatan</h3>
        <div className="h-64 w-full">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
              <XAxis dataKey="name" tick={{ fontSize: 12, fill: '#64748b' }} />
              <YAxis tick={{ fontSize: 12, fill: '#64748b' }} />
              <Tooltip />
              <Bar dataKey="value" radius={[6, 6, 0, 0]}>
                {chartData.map((_, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </div>
      </Card>
    </div>
  );
};

// --- Admin Component ---

const AdminView = ({ data, onRefresh }: { data: any, onRefresh: () => void }) => {
  const [name, setName] = useState('');
const [position, setPosition] = useState('');
const [email, setEmail] = useState('');
const [role, setRole] = useState<Role>('Staff');

  const handleAddUser = async (e: React.FormEvent) => {
    e.preventDefault();
    await api.createUser({
  name,
  position,
  email,
  role,
  password
});
    toast.success('Pekerja baharu berjaya didaftarkan.');
    setName('');
    setEmail('');
    onRefresh();
  };

  const handleDeleteUser = async (userId: string) => {
    if (window.confirm('Adakah anda pasti mahu memadam pekerja ini?')) {
      await api.deleteUser(userId);
      toast.success('Pekerja berjaya dipadam.');
      onRefresh();
    }
  };

  return (
    <div className="space-y-6">
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold">Daftar Pekerja Baharu</CardTitle>
          <CardDescription className="text-[#64748b]">Masukkan maklumat pekerja untuk didaftarkan ke dalam sistem.</CardDescription>
        </CardHeader>
        <CardContent className="p-6">
          <form onSubmit={handleAddUser} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Nama Penuh</Label>
              <Input className="border-[#e2e8f0]" value={name} onChange={(e) => setName(e.target.value)} required />
              <TableCell className="text-[13px] text-[#64748b]">
  {u.position || '-'}
</TableCell>
            </div>
            <div className="space-y-2">
  <Label className="text-[12px] font-bold text-[#64748b] uppercase">
    Jawatan
  </Label>

  <Input
    className="border-[#e2e8f0]"
    value={position}
    onChange={(e) => setPosition(e.target.value)}
    placeholder="Contoh: Pegawai Perancang"
    required
  />
</div>
            <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">
  Jawatan
</TableHead>
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Emel</Label>
              <Input className="border-[#e2e8f0]" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Peranan</Label>
              <Select value={role} onValueChange={(v: Role) => setRole(v)}>
                <SelectTrigger className="border-[#e2e8f0]">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Staff">Staff</SelectItem>
                  <SelectItem value="Pengarah">Pengarah</SelectItem>
                  <SelectItem value="Admin">Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <Button type="submit" className="bg-[#2563eb] hover:bg-[#1d4ed8]">Daftar Pekerja</Button>
          </form>
        </CardContent>
      </Card>

      <Card className="border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold">Senarai Pekerja Registered</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow className="hover:bg-transparent border-b border-[#e2e8f0]">
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6">Nama</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Emel</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Peranan</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6 text-right">Tindakan</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {data.users.map((u: any) => (
                <TableRow key={u.id} className="border-b border-[#e2e8f0] hover:bg-slate-50/50">
                  <TableCell className="px-6 py-4 font-bold text-[13px] text-[#0f172a]">{u.name}</TableCell>
                  <TableCell className="text-[13px] text-[#64748b]">{u.email}</TableCell>
                  <TableCell>
                    <Badge variant="outline" className="text-[11px] uppercase bg-slate-100 font-bold text-[#2563eb] border-slate-200">
                      {u.role}
                    </Badge>
                  </TableCell>
                  <TableCell className="px-6 text-right">
                    <Button 
                      variant="ghost" 
                      size="icon" 
                      className="h-8 w-8 text-[#64748b] hover:text-[#ef4444]"
                      onClick={() => handleDeleteUser(u.id)}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
};

// --- Staff View ---

const StaffView = ({ user, data, onRefresh }: { user: User, data: any, onRefresh: () => void }) => {
  const [projectName, setProjectName] = useState('');
  const [projectDesc, setProjectDesc] = useState('');
  const [projectCreatedAt, setProjectCreatedAt] = useState(new Date().toISOString().split('T')[0]);
  const [projectEndDate, setProjectEndDate] = useState('');
  const [projectStatus, setProjectStatus] = useState<ProjectStatus>('Dalam Perancangan');
  const [projectStaffIds, setProjectStaffIds] = useState<string[]>([user.id]);
  
  const [editingProject, setEditingProject] = useState<Project | null>(null);
  const [editName, setEditName] = useState('');
  const [editDesc, setEditDesc] = useState('');
  const [editCreatedAt, setEditCreatedAt] = useState('');
  const [editEndDate, setEditEndDate] = useState('');
  const [editStatus, setEditStatus] = useState<ProjectStatus>('Dalam Perancangan');
  const [editStaffIds, setEditStaffIds] = useState<string[]>([]);

  const toggleStaff = (id: string, isEdit: boolean = false) => {
    if (isEdit) {
      setEditStaffIds(prev => 
        prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
      );
    } else {
      setProjectStaffIds(prev => 
        prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
      );
    }
  };

  const handleAddProject = async (e: React.FormEvent) => {
    e.preventDefault();
    if (projectStaffIds.length === 0) {
      toast.error('Sila pilih sekurang-kurangnya seorang staff.');
      return;
    }
    await api.createProject({ 
      name: projectName, 
      description: projectDesc, 
      staffIds: projectStaffIds,
      createdAt: new Date(projectCreatedAt).toISOString(),
      endDate: new Date(projectEndDate).toISOString(),
      status: projectStatus
    });
    toast.success('Tugasan berjaya ditambah.');
    setProjectName('');
    setProjectDesc('');
    setProjectEndDate('');
    setProjectStaffIds([user.id]);
    onRefresh();
  };

  const handleUpdateProject = async (projectId: string, updates: Partial<Project>) => {
    await api.updateProject(projectId, updates);
    toast.success('Tugasan dikemaskini.');
    setEditingProject(null);
    onRefresh();
  };

  const handleOpenEdit = (p: Project) => {
    setEditingProject(p);
    setEditName(p.name);
    setEditDesc(p.description);
    setEditCreatedAt(p.createdAt.split('T')[0]);
    setEditEndDate(p.endDate ? p.endDate.split('T')[0] : '');
    setEditStatus(p.status || 'Dalam Perancangan');
    setEditStaffIds(p.staffIds || []);
  };

  const handleSaveEdit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingProject) return;
    if (editStaffIds.length === 0) {
      toast.error('Sila pilih sekurang-kurangnya seorang staff.');
      return;
    }
    await handleUpdateProject(editingProject.id, {
      name: editName,
      description: editDesc,
      staffIds: editStaffIds,
      createdAt: new Date(editCreatedAt).toISOString(),
      endDate: new Date(editEndDate).toISOString(),
      status: editStatus
    });
  };

  const handleDeleteProject = async (projectId: string) => {
    if (window.confirm('Adakah anda pasti mahu memadam tugasan ini? Semua data berkaitan juga akan dipadam.')) {
      await api.deleteProject(projectId);
      toast.success('Tugasan berjaya dipadam.');
      onRefresh();
    }
  };

  const myTasks = data.tasks.filter((t: any) => t.assignedTo === user.id);
  const allProjects = data.projects;

  return (
    <div className="space-y-6">
      <Tabs defaultValue="projects" className="w-full">
        <TabsList className="bg-white border border-[#e2e8f0] p-1 h-auto rounded-xl mb-6 shadow-sm">
          <TabsTrigger value="projects" className="rounded-lg px-6 py-2.5 data-[state=active]:bg-[#2563eb] data-[state=active]:text-white font-semibold">
            Senarai Tugasan Jabatan
          </TabsTrigger>
          <TabsTrigger value="timeline" className="rounded-lg px-6 py-2.5 data-[state=active]:bg-[#2563eb] data-[state=active]:text-white font-semibold">
            Timeline & Peringatan
          </TabsTrigger>
        </TabsList>

        <TabsContent value="projects" className="space-y-6 outline-none">
          <Card className="border-[#e2e8f0] shadow-sm rounded-xl">
            <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
              <CardTitle className="text-lg font-bold">Tambah Tugasan Baharu</CardTitle>
            </CardHeader>
            <CardContent className="p-6">
              <form onSubmit={handleAddProject} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label className="text-[12px] font-bold text-[#64748b] uppercase">Nama Tugasan</Label>
                    <Input className="border-[#e2e8f0]" value={projectName} onChange={(e) => setProjectName(e.target.value)} required />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-[12px] font-bold text-[#64748b] uppercase">Deskripsi Tugasan</Label>
                    <Input className="border-[#e2e8f0]" value={projectDesc} onChange={(e) => setProjectDesc(e.target.value)} required />
                  </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div className="space-y-2">
                    <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Dibuat</Label>
                    <Input className="border-[#e2e8f0]" type="date" value={projectCreatedAt} onChange={(e) => setProjectCreatedAt(e.target.value)} required />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Akhir Tugasan</Label>
                    <Input className="border-[#e2e8f0]" type="date" value={projectEndDate} onChange={(e) => setProjectEndDate(e.target.value)} required />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-[12px] font-bold text-[#64748b] uppercase">Status Kemajuan</Label>
                    <Select value={projectStatus} onValueChange={(v: ProjectStatus) => setProjectStatus(v)}>
                      <SelectTrigger className="border-[#e2e8f0]">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Dalam Perancangan">Dalam Perancangan</SelectItem>
                        <SelectItem value="Dalam Proses">Dalam Proses</SelectItem>
                        <SelectItem value="Belum Selesai">Belum Selesai</SelectItem>
                        <SelectItem value="Selesai">Selesai</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                <div className="space-y-3">
                  <Label className="text-[12px] font-bold text-[#64748b] uppercase">Pilih Staff Terlibat</Label>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    {data.users.filter((u: any) => u.role === 'Staff').map((u: any) => (
                      <div 
                        key={u.id}
                        onClick={() => toggleStaff(u.id)}
                        className={`flex items-center p-3 rounded-lg border cursor-pointer transition-all ${
                          projectStaffIds.includes(u.id) 
                            ? 'border-[#2563eb] bg-blue-50/80 ring-1 ring-[#2563eb]' 
                            : 'border-[#e2e8f0] hover:border-[#2563eb]/50 hover:bg-slate-50'
                        }`}
                      >
                        <Avatar className="h-8 w-8 mr-3">
  <AvatarFallback className="bg-slate-100 text-slate-500">
    <UserCircle className="h-5 w-5" />
  </AvatarFallback>
</Avatar>
                        <div className="flex-1 min-w-0">
                          <p className={`text-[13px] font-semibold truncate ${projectStaffIds.includes(u.id) ? 'text-[#2563eb]' : 'text-[#0f172a]'}`}>
                            {u.name}
                          </p>
                          <p className="text-[10px] text-[#64748b] truncate">{u.email}</p>
                        </div>
                        {projectStaffIds.includes(u.id) && (
                          <div className="h-4 w-4 bg-[#2563eb] rounded-full flex items-center justify-center">
                            <CheckSquare className="h-3 w-3 text-white" />
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
                <Button type="submit" className="bg-[#2563eb] hover:bg-[#1d4ed8] font-bold w-full sm:w-auto">
                  <Plus className="w-4 h-4 mr-2" /> Tambah Tugasan Baharu
                </Button>
              </form>
            </CardContent>
          </Card>

          <Card className="border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden">
            <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
              <CardTitle className="text-lg font-bold">Senarai Tugasan Jabatan</CardTitle>
            </CardHeader>
            <CardContent className="p-0">
              <Table>
                <TableHeader className="bg-[#f8fafc]">
                  <TableRow className="hover:bg-transparent border-b border-[#e2e8f0]">
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6">Tugasan</TableHead>
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Staff Terlibat</TableHead>
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Tarikh Dibuat</TableHead>
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Tarikh Akhir</TableHead>
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Status</TableHead>
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Deskripsi</TableHead>
                    <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6 text-right">Tindakan</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {allProjects.map((p: any) => (
                    <TableRow key={p.id} className="border-b border-[#e2e8f0] hover:bg-slate-50/50">
                      <TableCell className="px-6 py-4 font-bold text-[13px] text-[#0f172a]">{p.name}</TableCell>
                      <TableCell className="text-[13px] text-[#64748b]">
                        <div className="flex flex-wrap gap-1">
                          {p.staffIds?.map((sid: string) => (
                            <Badge key={sid} variant="outline" className="text-[10px] py-0 h-5 bg-white">
                              {data.users.find((u: any) => u.id === sid)?.name || sid}
                            </Badge>
                          )) || (data.users.find((u: any) => u.id === p.staffId)?.name || '-')}
                        </div>
                      </TableCell>
                      <TableCell className="text-[13px] text-[#64748b]">{format(new Date(p.createdAt), 'dd MMM yyyy')}</TableCell>
                      <TableCell className="text-[13px] text-[#64748b]">{p.endDate ? format(new Date(p.endDate), 'dd MMM yyyy') : '-'}</TableCell>
                      <TableCell>
                        <Select 
                          value={p.status || 'Dalam Perancangan'} 
                          onValueChange={async (v: ProjectStatus) => {
                            await api.updateProject(p.id, { status: v });
                            toast.success('Status tugasan dikemaskini.');
                            onRefresh();
                          }}
                        >
                          <SelectTrigger className="border-none shadow-none p-0 h-auto bg-transparent focus:ring-0">
                            <span className={`status-pill ${
                              p.status === 'Selesai' ? 'status-done' : 
                              p.status === 'Dalam Proses' ? 'status-progress' : 
                              p.status === 'Belum Selesai' ? 'status-pending' : 'status-info'
                            }`}>
                              {p.status || 'Dalam Perancangan'}
                            </span>
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="Dalam Perancangan">Dalam Perancangan</SelectItem>
                            <SelectItem value="Dalam Proses">Dalam Proses</SelectItem>
                            <SelectItem value="Belum Selesai">Belum Selesai</SelectItem>
                            <SelectItem value="Selesai">Selesai</SelectItem>
                          </SelectContent>
                        </Select>
                      </TableCell>
                      <TableCell className="text-[13px] text-[#64748b] max-w-xs truncate">{p.description}</TableCell>
                      <TableCell className="px-6 text-right">
                        <div className="flex items-center justify-end gap-2">
                          {(p.staffIds?.includes(user.id) || p.staffId === user.id || user.role === 'Admin') && (
                            <>
                              <Button 
                                variant="ghost" 
                                size="icon" 
                                className="h-8 w-8 text-[#64748b] hover:text-[#2563eb]"
                                onClick={() => handleOpenEdit(p)}
                              >
                                <Pencil className="h-4 w-4" />
                              </Button>
                              <Button 
                                variant="ghost" 
                                size="icon" 
                                className="h-8 w-8 text-[#64748b] hover:text-[#ef4444]"
                                onClick={() => handleDeleteProject(p.id)}
                              >
                                <Trash2 className="h-4 w-4" />
                              </Button>
                            </>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                  {allProjects.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={7} className="text-center py-12 text-[#64748b] text-sm">Tiada projek untuk dipaparkan.</TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="timeline" className="outline-none">
          <Card className="border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden">
            <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
              <CardTitle className="text-lg font-bold">Timeline Tugasan Saya</CardTitle>
              <CardDescription className="text-[#64748b]">Peringatan emel akan dihantar secara automatik apabila tugasan menghampiri deadline.</CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              <div className="divide-y divide-[#e2e8f0]">
                {myTasks.sort((a: any, b: any) => new Date(a.deadline).getTime() - new Date(b.deadline).getTime()).map((task: any) => {
                  const daysLeft = differenceInDays(new Date(task.deadline), new Date());
                  const isUrgent = daysLeft <= 3 && task.status !== 'Completed';
                  
                  return (
                    <div key={task.id} className="timeline-item">
                      <div className="flex items-center justify-between">
                        <div className="space-y-1">
                          <p className="font-bold text-[#0f172a]">{task.title}</p>
                          <div className="flex flex-col gap-0.5">
                            <p className="text-[11px] text-[#64748b]">Mula: {task.startDate ? format(new Date(task.startDate), 'dd MMM yyyy') : '-'}</p>
                            <p className="text-[11px] text-[#64748b]">Tamat: {format(new Date(task.deadline), 'dd MMM yyyy')}</p>
                          </div>
                        </div>
                        <div className="text-right">
                          {isUrgent ? (
                            <div className="flex flex-col items-end">
                              <span className="text-[11px] font-bold text-[#ef4444] flex items-center gap-1">
                                ⚠️ {daysLeft < 0 ? 'Tamat Tempoh' : `${daysLeft} hari lagi`}
                              </span>
                              <p className="text-[10px] text-[#ef4444]/70 mt-1">Emel amaran automatik dihantar.</p>
                            </div>
                          ) : (
                            <span className="text-[12px] font-bold text-[#2563eb]">{daysLeft} hari lagi</span>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                })}
                {myTasks.length === 0 && <p className="text-center py-12 text-[#64748b] text-sm">Tiada timeline untuk dipaparkan.</p>}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      <Dialog open={!!editingProject} onOpenChange={(open) => !open && setEditingProject(null)}>
        <DialogContent className="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>Kemaskini Tugasan</DialogTitle>
            <DialogDescription>
              Ubah maklumat tugasan anda di sini. Klik simpan apabila selesai.
            </DialogDescription>
          </DialogHeader>
          <form onSubmit={handleSaveEdit} className="space-y-4 py-4">
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Nama Tugasan</Label>
              <Input className="border-[#e2e8f0]" value={editName} onChange={(e) => setEditName(e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Deskripsi Tugasan</Label>
              <Input className="border-[#e2e8f0]" value={editDesc} onChange={(e) => setEditDesc(e.target.value)} required />
            </div>
            <div className="grid grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Dibuat</Label>
                <Input className="border-[#e2e8f0]" type="date" value={editCreatedAt} onChange={(e) => setEditCreatedAt(e.target.value)} required />
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Akhir</Label>
                <Input className="border-[#e2e8f0]" type="date" value={editEndDate} onChange={(e) => setEditEndDate(e.target.value)} required />
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Status</Label>
                <Select value={editStatus} onValueChange={(v: ProjectStatus) => setEditStatus(v)}>
                  <SelectTrigger className="border-[#e2e8f0]">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Dalam Perancangan">Dalam Perancangan</SelectItem>
                    <SelectItem value="Dalam Proses">Dalam Proses</SelectItem>
                    <SelectItem value="Belum Selesai">Belum Selesai</SelectItem>
                    <SelectItem value="Selesai">Selesai</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div className="space-y-3">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Pilih Staff Terlibat</Label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {data.users.filter((u: any) => u.role === 'Staff').map((u: any) => (
                  <div 
                    key={u.id}
                    onClick={() => toggleStaff(u.id, true)}
                    className={`flex items-center p-3 rounded-lg border cursor-pointer transition-all ${
                      editStaffIds.includes(u.id) 
                        ? 'border-[#2563eb] bg-blue-50 ring-1 ring-[#2563eb]' 
                        : 'border-[#e2e8f0] hover:border-[#2563eb]/50 hover:bg-slate-50'
                    }`}
                  >
                    <Avatar className="h-8 w-8 mr-3">
                      <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${u.name}`} />
                      <AvatarFallback>{u.name[0]}</AvatarFallback>
                    </Avatar>
                    <div className="flex-1 min-w-0">
                      <p className={`text-[13px] font-semibold truncate ${editStaffIds.includes(u.id) ? 'text-[#2563eb]' : 'text-[#0f172a]'}`}>
                        {u.name}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <DialogFooter className="pt-4">
              <Button type="button" variant="outline" onClick={() => setEditingProject(null)}>Batal</Button>
              <Button type="submit" className="bg-[#2563eb]">Simpan Perubahan</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  );
};

// --- Pengarah View ---

const PengarahView = ({ data, onRefresh }: { data: any, onRefresh: () => void }) => {
  const [taskTitle, setTaskTitle] = useState('');
  const [taskDesc, setTaskDesc] = useState('');
  const [projectId, setProjectId] = useState('');
  const [assignedTo, setAssignedTo] = useState('');
  const [startDate, setStartDate] = useState('');
  const [deadline, setDeadline] = useState('');

  const [editingProject, setEditingProject] = useState<Project | null>(null);
  const [editName, setEditName] = useState('');
  const [editDesc, setEditDesc] = useState('');
  const [editCreatedAt, setEditCreatedAt] = useState('');
  const [editEndDate, setEditEndDate] = useState('');
  const [editStatus, setEditStatus] = useState<ProjectStatus>('Dalam Perancangan');
  const [editStaffIds, setEditStaffIds] = useState<string[]>([]);

  const toggleStaff = (id: string) => {
    setEditStaffIds(prev => 
      prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
    );
  };

  const handleOpenEdit = (p: Project) => {
    setEditingProject(p);
    setEditName(p.name);
    setEditDesc(p.description);
    setEditCreatedAt(p.createdAt.split('T')[0]);
    setEditEndDate(p.endDate ? p.endDate.split('T')[0] : '');
    setEditStatus(p.status || 'Dalam Perancangan');
    setEditStaffIds(p.staffIds || []);
  };

  const handleSaveEdit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingProject) return;
    await api.updateProject(editingProject.id, {
      name: editName,
      description: editDesc,
      staffIds: editStaffIds,
      createdAt: new Date(editCreatedAt).toISOString(),
      endDate: new Date(editEndDate).toISOString(),
      status: editStatus
    });
    toast.success('Tugasan dikemaskini.');
    setEditingProject(null);
    onRefresh();
  };

  const handleAssignTask = async (e: React.FormEvent) => {
    e.preventDefault();
    await api.createTask({ 
      title: taskTitle, 
      description: taskDesc, 
      projectId, 
      assignedTo, 
      startDate: new Date(startDate).toISOString(),
      deadline: new Date(deadline).toISOString() 
    });
    toast.success('Sub-tugasan berjaya diberikan.');
    setTaskTitle('');
    setTaskDesc('');
    onRefresh();
  };

  const handleDeleteTask = async (taskId: string) => {
    if (window.confirm('Adakah anda pasti mahu memadam tugasan ini?')) {
      await api.deleteTask(taskId);
      toast.success('Sub-tugasan berjaya dipadam.');
      onRefresh();
    }
  };

  return (
    <div className="space-y-6">
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold">Berikan Sub-Tugasan Baharu</CardTitle>
          <CardDescription className="text-[#64748b]">Pilih tugasan utama dan pekerja untuk memberikan tugasan spesifik.</CardDescription>
        </CardHeader>
        <CardContent className="p-6">
          <form onSubmit={handleAssignTask} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tajuk Tugasan</Label>
                <Input className="border-[#e2e8f0]" value={taskTitle} onChange={(e) => setTaskTitle(e.target.value)} required />
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Mula</Label>
                <Input className="border-[#e2e8f0]" type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} required />
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Deadline</Label>
                <Input className="border-[#e2e8f0]" type="date" value={deadline} onChange={(e) => setDeadline(e.target.value)} required />
              </div>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Pilih Tugasan Utama</Label>
                <Select value={projectId} onValueChange={setProjectId}>
                  <SelectTrigger className="border-[#e2e8f0]">
                    <SelectValue placeholder="Pilih Tugasan" />
                  </SelectTrigger>
                  <SelectContent>
                    {data.projects.map((p: any) => (
                      <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Berikan Kepada</Label>
                <Select value={assignedTo} onValueChange={setAssignedTo}>
                  <SelectTrigger className="border-[#e2e8f0]">
                    <SelectValue placeholder="Pilih Pekerja" />
                  </SelectTrigger>
                  <SelectContent>
                    {data.users.filter((u: any) => u.role === 'Staff').map((u: any) => (
                      <SelectItem key={u.id} value={u.id}>{u.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Deskripsi Tugasan</Label>
              <Input className="border-[#e2e8f0]" value={taskDesc} onChange={(e) => setTaskDesc(e.target.value)} required />
            </div>
            <Button type="submit" className="bg-[#2563eb] hover:bg-[#1d4ed8]">Berikan Tugasan</Button>
          </form>
        </CardContent>
      </Card>

      <Card className="border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold">Senarai Tugasan & Staff Terlibat</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow className="hover:bg-transparent border-b border-[#e2e8f0]">
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6">Tugasan</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Staff Bertanggungjawab</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Tarikh Mula</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Tarikh Akhir</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6">Status</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6 text-right">Tindakan</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {data.projects.map((p: any) => (
                <TableRow key={p.id} className="border-b border-[#e2e8f0] hover:bg-slate-50/50">
                  <TableCell className="px-6 py-4 font-bold text-[13px] text-[#0f172a]">{p.name}</TableCell>
                  <TableCell className="text-[13px] text-[#64748b]">
                    <div className="flex flex-wrap gap-1">
                      {p.staffIds?.map((sid: string) => (
                        <Badge key={sid} variant="outline" className="text-[10px] py-0 h-5 bg-white">
                          {data.users.find((u: any) => u.id === sid)?.name || sid}
                        </Badge>
                      )) || (data.users.find((u: any) => u.id === p.staffId)?.name || '-')}
                    </div>
                  </TableCell>
                  <TableCell className="text-[13px] text-[#64748b]">{format(new Date(p.createdAt), 'dd MMM yyyy')}</TableCell>
                  <TableCell className="text-[13px] text-[#64748b]">{p.endDate ? format(new Date(p.endDate), 'dd MMM yyyy') : '-'}</TableCell>
                  <TableCell className="px-6">
                    <Select 
                      value={p.status || 'Dalam Perancangan'} 
                      onValueChange={async (v: ProjectStatus) => {
                        await api.updateProject(p.id, { status: v });
                        toast.success('Status tugasan dikemaskini.');
                        onRefresh();
                      }}
                    >
                      <SelectTrigger className="border-none shadow-none p-0 h-auto bg-transparent focus:ring-0">
                        <span className={`status-pill ${
                          p.status === 'Selesai' ? 'status-done' : 
                          p.status === 'Dalam Proses' ? 'status-progress' : 
                          p.status === 'Belum Selesai' ? 'status-pending' : 'status-info'
                        }`}>
                          {p.status || 'Dalam Perancangan'}
                        </span>
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Dalam Perancangan">Dalam Perancangan</SelectItem>
                        <SelectItem value="Dalam Proses">Dalam Proses</SelectItem>
                        <SelectItem value="Belum Selesai">Belum Selesai</SelectItem>
                        <SelectItem value="Selesai">Selesai</SelectItem>
                      </SelectContent>
                    </Select>
                  </TableCell>
                  <TableCell className="px-6 text-right">
                    <Button 
                      variant="ghost" 
                      size="icon" 
                      className="h-8 w-8 text-[#64748b] hover:text-[#2563eb]"
                      onClick={() => handleOpenEdit(p)}
                    >
                      <Pencil className="h-4 w-4" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {data.projects.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-12 text-[#64748b] text-sm">Tiada projek untuk dipaparkan.</TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Dialog open={!!editingProject} onOpenChange={(open) => !open && setEditingProject(null)}>
        <DialogContent className="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>Kemaskini Tugasan Jabatan</DialogTitle>
            <DialogDescription>
              Ubah maklumat tugasan di sini.
            </DialogDescription>
          </DialogHeader>
          <form onSubmit={handleSaveEdit} className="space-y-4 py-4">
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Nama Tugasan</Label>
              <Input className="border-[#e2e8f0]" value={editName} onChange={(e) => setEditName(e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Deskripsi Tugasan</Label>
              <Input className="border-[#e2e8f0]" value={editDesc} onChange={(e) => setEditDesc(e.target.value)} required />
            </div>
            <div className="grid grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Dibuat</Label>
                <Input className="border-[#e2e8f0]" type="date" value={editCreatedAt} onChange={(e) => setEditCreatedAt(e.target.value)} required />
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Tarikh Akhir</Label>
                <Input className="border-[#e2e8f0]" type="date" value={editEndDate} onChange={(e) => setEditEndDate(e.target.value)} required />
              </div>
              <div className="space-y-2">
                <Label className="text-[12px] font-bold text-[#64748b] uppercase">Status</Label>
                <Select value={editStatus} onValueChange={(v: ProjectStatus) => setEditStatus(v)}>
                  <SelectTrigger className="border-[#e2e8f0]">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Dalam Perancangan">Dalam Perancangan</SelectItem>
                    <SelectItem value="Dalam Proses">Dalam Proses</SelectItem>
                    <SelectItem value="Belum Selesai">Belum Selesai</SelectItem>
                    <SelectItem value="Selesai">Selesai</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div className="space-y-3">
              <Label className="text-[12px] font-bold text-[#64748b] uppercase">Pilih Staff Terlibat</Label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {data.users.filter((u: any) => u.role === 'Staff').map((u: any) => (
                  <div 
                    key={u.id}
                    onClick={() => toggleStaff(u.id)}
                    className={`flex items-center p-3 rounded-lg border cursor-pointer transition-all ${
                      editStaffIds.includes(u.id) 
                        ? 'border-[#2563eb] bg-blue-50 ring-1 ring-[#2563eb]' 
                        : 'border-[#e2e8f0] hover:border-[#2563eb]/50 hover:bg-slate-50'
                    }`}
                  >
                    <Avatar className="h-8 w-8 mr-3">
                      <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${u.name}`} />
                      <AvatarFallback>{u.name[0]}</AvatarFallback>
                    </Avatar>
                    <div className="flex-1 min-w-0">
                      <p className={`text-[13px] font-semibold truncate ${editStaffIds.includes(u.id) ? 'text-[#2563eb]' : 'text-[#0f172a]'}`}>
                        {u.name}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <DialogFooter className="pt-4">
              <Button type="button" variant="outline" onClick={() => setEditingProject(null)}>Batal</Button>
              <Button type="submit" className="bg-[#2563eb]">Simpan Perubahan</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <Card className="border-[#e2e8f0] shadow-sm rounded-xl overflow-hidden">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold">Pantau Sub-Tugasan Semasa</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow className="hover:bg-transparent border-b border-[#e2e8f0]">
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6">Tugasan Utama</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Sub-Tugasan</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Pekerja</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Status</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Tarikh Mula</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10">Deadline</TableHead>
                <TableHead className="text-[10px] uppercase font-bold text-[#64748b] h-10 px-6 text-right">Tindakan</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {data.tasks.map((task: any) => {
                const project = data.projects.find((p: any) => p.id === task.projectId);
                const assignedUser = data.users.find((u: any) => u.id === task.assignedTo);
                return (
                  <TableRow key={task.id} className="border-b border-[#e2e8f0] hover:bg-slate-50/50">
                    <TableCell className="px-6 py-4 font-bold text-[13px] text-[#0f172a]">{project?.name || '-'}</TableCell>
                    <TableCell className="text-[13px] text-[#64748b]">{task.title}</TableCell>
                    <TableCell className="text-[13px] text-[#64748b]">{assignedUser?.name || '-'}</TableCell>
                    <TableCell>
                      <Badge 
                        variant="outline" 
                        className={`text-[10px] ${
                          task.status === 'Completed' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' :
                          task.status === 'In Progress' ? 'bg-blue-50 text-blue-600 border-blue-200' :
                          'bg-amber-50 text-amber-600 border-amber-200'
                        }`}
                      >
                        {task.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-[13px] text-[#64748b]">{task.startDate ? format(new Date(task.startDate), 'dd MMM yyyy') : '-'}</TableCell>
                    <TableCell className="text-[13px] text-[#64748b]">{format(new Date(task.deadline), 'dd MMM yyyy')}</TableCell>
                    <TableCell className="px-6 text-right">
                      <Button 
                        variant="ghost" 
                        size="icon" 
                        className="h-8 w-8 text-[#64748b] hover:text-[#ef4444]"
                        onClick={() => handleDeleteTask(task.id)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
};

// --- Reports View ---

const Reports = ({ data }: { data: any }) => {
  const generateReport = (title: string) => {
    toast.success(`Menjana ${title}... Laporan telah dimuat turun secara automatik.`);
  };

  return (
    <div className="space-y-6">
      <Card className="border-[#e2e8f0] shadow-sm rounded-xl">
        <CardHeader className="px-6 py-5 border-b border-[#e2e8f0]">
          <CardTitle className="text-lg font-bold">Pengurusan Laporan & Muat Turun</CardTitle>
          <CardDescription className="text-[#64748b]">Jana laporan prestasi tugasan, staf, dan rekod jabatan.</CardDescription>
        </CardHeader>
        <CardContent className="p-6 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card className="bg-slate-50 border border-slate-200 shadow-none rounded-xl">
              <CardHeader className="pb-2">
                <CardTitle className="text-base font-bold text-[#0f172a]">Laporan Bulanan Jabatan</CardTitle>
                <CardDescription className="text-xs">Ringkasan status tugasan bulanan</CardDescription>
              </CardHeader>
              <CardContent className="pt-2">
                <Button 
                  className="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white" 
                  onClick={() => generateReport('Laporan Bulanan Jabatan')}
                >
                  <FileText className="w-4 h-4 mr-2" /> Muat Turun PDF
                </Button>
              </CardContent>
            </Card>

            <Card className="bg-slate-50 border border-slate-200 shadow-none rounded-xl">
              <CardHeader className="pb-2">
                <CardTitle className="text-base font-bold text-[#0f172a]">Prestasi & KPI Pekerja</CardTitle>
                <CardDescription className="text-xs">Statistik tugasan mengikut staf</CardDescription>
              </CardHeader>
              <CardContent className="pt-2">
                <Button 
                  className="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white" 
                  onClick={() => generateReport('Laporan Prestasi Pekerja')}
                >
                  <FileText className="w-4 h-4 mr-2" /> Muat Turun Excel
                </Button>
              </CardContent>
            </Card>

            <Card className="bg-slate-50 border border-slate-200 shadow-none rounded-xl">
              <CardHeader className="pb-2">
                <CardTitle className="text-base font-bold text-[#0f172a]">Ringkasan Status Tugasan</CardTitle>
                <CardDescription className="text-xs">Analisis kemajuan tugasan aktif</CardDescription>
              </CardHeader>
              <CardContent className="pt-2">
                <Button 
                  className="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white" 
                  onClick={() => generateReport('Ringkasan Status Tugasan')}
                >
                  <FileText className="w-4 h-4 mr-2" /> Jana Laporan Ringkas
                </Button>
              </CardContent>
            </Card>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

// --- Main Web Portal App ---

export default function App() {
  const [user, setUser] = useState<User | null>(null);
  const [data, setData] = useState<any>(null);
  const [activeTab, setActiveTab] = useState('home');
  const [isLoginModalOpen, setIsLoginModalOpen] = useState(false);
  const [fontSize, setFontSize] = useState<'sm' | 'md' | 'lg'>('md');
  const [currentTime, setCurrentTime] = useState(new Date());

  const fetchData = async () => {
    const res = await api.getData();
    setData(res);
  };

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
  const timer = window.setInterval(() => {
    setCurrentTime(new Date());
  }, 1000);

  return () => {
    window.clearInterval(timer);
  };
}, []);

  if (!data) {
    return (
      <div className="flex flex-col items-center justify-center h-screen bg-[#f8fafc] space-y-4">
        <div className="w-12 h-12 border-4 border-[#2563eb] border-t-transparent rounded-full animate-spin" />
        <p className="text-sm font-semibold text-[#64748b]">Memuatkan Portal PLANMalaysia Perlis...</p>
      </div>
    );
  }

  const allMenuItems = [
    { id: 'home', label: 'Halaman Utama Portal', icon: Home, roles: ['All'] },
    { id: 'dashboard', label: 'Dashboard Utama', icon: LayoutDashboard, roles: ['Admin', 'Staff', 'Pengarah'] },
    { id: 'staff', label: 'Senarai Tugasan Jabatan', icon: Briefcase, roles: ['Staff'] },
    { id: 'pengarah', label: 'Pantauan Tugasan', icon: CheckSquare, roles: ['Pengarah'] },
    { id: 'admin', label: 'Pengurusan Staf', icon: Users, roles: ['Admin'] },
    { id: 'reports', label: 'Laporan', icon: FileText, roles: ['Admin', 'Pengarah', 'Staff'] },
    { id: 'directory', label: 'Direktori Staf', icon: UserCheck, roles: ['All'] },
  ];

  const visibleMenuItems = allMenuItems.filter(item => {
    if (item.roles.includes('All')) return true;
    if (!user) return false;
    return item.roles.includes(user.role);
  });

  const fontClass = fontSize === 'sm' ? 'text-xs' : fontSize === 'lg' ? 'text-base' : 'text-sm';

  return (
    <div className={`min-h-screen bg-[#f1f5f9] flex flex-col font-sans ${fontClass}`}>
      {/* 1. Top Government Utility Bar */}
      <div className="bg-[#0f172a] text-slate-300 py-1.5 px-4 md:px-8 text-[11px] border-b border-slate-800 flex flex-wrap items-center justify-between gap-2">
        <div className="flex items-center gap-3">
          <span className="flex items-center gap-1 font-bold text-amber-400">
            <Shield className="w-3.5 h-3.5" /> KERAJAAN NEGERI PERLIS
          </span>
          <span className="hidden sm:inline text-slate-500">|</span>
          <span className="hidden sm:inline text-slate-300 font-medium">
            Jabatan Perancangan Bandar dan Desa Negeri Perlis (PLANMalaysia Perlis)
          </span>
        </div>

        <div className="flex items-center gap-4">
          <div className="hidden md:flex items-center gap-1.5 text-slate-400">
            <Clock className="w-3 h-3 text-blue-400" />
            <span>
  {formatMalaysiaDate(currentTime)}
</span>

<span className="text-slate-500 hidden lg:inline">
  {formatMalaysiaTime(currentTime)}
</span>
          </div>

          <div className="flex items-center gap-1 bg-slate-800 rounded px-1.5 py-0.5">
            <button 
              onClick={() => setFontSize('sm')} 
              className={`px-1 rounded hover:bg-slate-700 ${fontSize === 'sm' ? 'text-blue-400 font-bold' : ''}`}
            >
              A-
            </button>
            <button 
              onClick={() => setFontSize('md')} 
              className={`px-1 rounded hover:bg-slate-700 ${fontSize === 'md' ? 'text-blue-400 font-bold' : ''}`}
            >
              A
            </button>
            <button 
              onClick={() => setFontSize('lg')} 
              className={`px-1 rounded hover:bg-slate-700 ${fontSize === 'lg' ? 'text-blue-400 font-bold' : ''}`}
            >
              A+
            </button>
          </div>

          {user ? (
            <div className="flex items-center gap-2 border-l border-slate-700 pl-3">
              <span className="text-amber-300 font-bold uppercase text-[10px] bg-amber-900/50 px-2 py-0.5 rounded">
                {user.role}
              </span>
              <span className="text-white font-semibold truncate max-w-[120px]">{user.name}</span>
              <button 
                onClick={() => {
                  setUser(null);
                  setActiveTab('home');
                  toast.info('Log keluar berjaya.');
                }}
                className="text-red-400 hover:text-red-300 font-medium ml-1 flex items-center gap-1"
              >
                <LogOut className="w-3 h-3" /> Log Keluar
              </button>
            </div>
          ) : (
            <button 
              onClick={() => setIsLoginModalOpen(true)}
              className="bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold px-3 py-0.5 rounded text-[10px] transition-colors flex items-center gap-1"
            >
              <UserCheck className="w-3 h-3" /> Log Masuk Staf
            </button>
          )}
        </div>
      </div>

      {/* 2. Official Portal Web Header Banner */}
      <header className="bg-white border-b border-[#e2e8f0] shadow-sm py-4 md:py-5 px-4 md:px-8">
        <div className="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-4">
          <div 
            className="flex flex-col sm:flex-row items-center gap-4 md:gap-6 cursor-pointer text-center sm:text-left"
            onClick={() => setActiveTab('home')}
          >
            <div className="p-1 rounded-lg bg-white shrink-0">
              <img 
                src={logoPMPerlis} 
                alt="Logo PLANMalaysia Perlis" 
                className="h-20 sm:h-24 md:h-28 lg:h-32 w-auto max-w-[340px] sm:max-w-none object-contain shrink-0 drop-shadow-sm transition-transform hover:scale-[1.02]" 
              />
            </div>
            <div className="border-t sm:border-t-0 sm:border-l sm:border-slate-200 pt-2 sm:pt-0 sm:pl-5">
              <h1 className="text-lg md:text-2xl font-extrabold text-[#0f172a] tracking-tight leading-tight">
                PORTAL RASMI PENGURUSAN TUGASAN JABATAN
              </h1>
              <p className="text-xs md:text-sm text-[#2563eb] font-bold">
                Jabatan Perancangan Bandar dan Desa Negeri Perlis (PLANMalaysia Perlis)
              </p>
            </div>
          </div>

          <div className="flex items-center gap-3 shrink-0">
            <div className="hidden lg:flex flex-col text-right">
              <span className="text-[10px] uppercase font-bold text-[#64748b]">Status Sistem Portal</span>
              <span className="text-xs font-bold text-emerald-600 flex items-center justify-end gap-1">
                <span className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" /> Portal Dalam Talian
              </span>
            </div>
            {!user && (
              <Button 
                className="bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold text-xs"
                onClick={() => setIsLoginModalOpen(true)}
              >
                Log Masuk Staf Portal
              </Button>
            )}
          </div>
        </div>
      </header>

      {/* 3. Official Website Navigation Bar */}
      <nav className="bg-[#1e293b] text-white sticky top-0 z-30 shadow-md border-b border-slate-700">
        <div className="max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between">
          <div className="flex items-center overflow-x-auto no-scrollbar space-x-1 py-1">
            {visibleMenuItems.map((item) => (
              <button
                key={item.id}
                onClick={() => {
                  if (item.roles.includes('All') || user) {
                    setActiveTab(item.id);
                  } else {
                    setIsLoginModalOpen(true);
                  }
                }}
                className={`px-4 py-3 rounded-lg text-xs font-bold whitespace-nowrap transition-all flex items-center gap-2 ${
                  activeTab === item.id 
                    ? 'bg-[#2563eb] text-white shadow-sm' 
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                }`}
              >
                <item.icon className="w-4 h-4" />
                <span>{item.label}</span>
              </button>
            ))}
          </div>

          {/* Quick Search shortcut */}
          <div className="hidden xl:flex items-center pl-4 border-l border-slate-700">
            <Button 
              size="sm" 
              variant="outline" 
              className="bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700 hover:text-white text-xs"
              onClick={() => {
                if (!user) setIsLoginModalOpen(true);
                else setActiveTab('staff');
              }}
            >
              <Plus className="w-3.5 h-3.5 mr-1" />
              {user ? 'Tambah Tugasan' : 'Akses Staf'}
            </Button>
          </div>
        </div>
      </nav>

      {/* 4. Main Body Content */}
      <main className="flex-1 max-w-7xl w-full mx-auto p-4 md:p-8">
        <AnimatePresence mode="wait">
          <motion.div
            key={activeTab}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -8 }}
            transition={{ duration: 0.15 }}
          >
            {activeTab === 'home' && (
              <PortalHome 
                data={data} 
                onLoginClick={() => setIsLoginModalOpen(true)}
                onViewTask={(tab) => {
                  if (user) setActiveTab(tab);
                  else setIsLoginModalOpen(true);
                }}
              />
            )}
            {activeTab === 'dashboard' && <Dashboard user={user!} data={data} />}
            {activeTab === 'staff' && <StaffView user={user!} data={data} onRefresh={fetchData} />}
            {activeTab === 'pengarah' && <PengarahView data={data} onRefresh={fetchData} />}
            {activeTab === 'admin' && <AdminView data={data} onRefresh={fetchData} />}
            {activeTab === 'reports' && <Reports data={data} />}
            {activeTab === 'directory' && <StaffDirectory users={data.users} />}
          </motion.div>
        </AnimatePresence>
      </main>

      {/* 5. Official Government Web Portal Footer */}
      <footer className="bg-[#0f172a] text-slate-400 text-xs border-t border-slate-800 mt-auto">
        <div className="max-w-7xl mx-auto px-4 md:px-8 py-10 grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Logo & About */}
          <div className="space-y-3 md:col-span-1">
            <div className="flex items-center gap-3">
              <div className="bg-white/95 p-1.5 rounded-lg shadow-sm shrink-0">
                <img src={logoPMPerlis} alt="Logo PLANMalaysia Perlis" className="h-10 sm:h-12 w-auto object-contain" />
              </div>
              <span className="font-extrabold text-white text-base">PLANMalaysia Perlis</span>
            </div>
            <p className="text-[11px] text-slate-400 leading-relaxed">
              Jabatan Perancangan Bandar dan Desa Negeri Perlis komited dalam memastikan perancangan guna tanah negeri secara mampan dan teratur.
            </p>
          </div>

          {/* Quick Links */}
          <div className="space-y-2">
            <p className="font-bold text-white text-xs uppercase tracking-wider">Pautan Portal</p>
            <ul className="space-y-1 text-[11px]">
              <li>
                <button onClick={() => setActiveTab('home')} className="hover:text-blue-400 transition-colors">
                  Halaman Utama
                </button>
              </li>
              <li>
                <button onClick={() => setActiveTab('directory')} className="hover:text-blue-400 transition-colors">
                  Direktori Pegawai
                </button>
              </li>
              <li>
                <button onClick={() => setIsLoginModalOpen(true)} className="hover:text-blue-400 transition-colors">
                  Log Masuk Staf
                </button>
              </li>
            </ul>
          </div>

          {/* Contact Info */}
          <div className="space-y-2">
            <p className="font-bold text-white text-xs uppercase tracking-wider">Hubungi Kami</p>
            <div className="space-y-1.5 text-[11px]">
              <p className="flex items-start gap-2">
                <MapPin className="w-3.5 h-3.5 text-blue-400 shrink-0 mt-0.5" />
                <span>Tingkat 1, Bangunan Dato' Mahmud Mat, 01000 Kangar, Perlis</span>
              </p>
              <p className="flex items-center gap-2">
                <Phone className="w-3.5 h-3.5 text-blue-400 shrink-0" />
                <span>04-976 1234 / 04-976 5678</span>
              </p>
              <p className="flex items-center gap-2">
                <Mail className="w-3.5 h-3.5 text-blue-400 shrink-0" />
                <span>planperlis@perlis.gov.my</span>
              </p>
            </div>
          </div>

          {/* Stats & Compliance */}
          <div className="space-y-2">
            <p className="font-bold text-white text-xs uppercase tracking-wider">Statistik Pelawat</p>
            <div className="bg-slate-800/80 p-3 rounded-lg border border-slate-700 space-y-1 text-[11px]">
              <div className="flex justify-between">
                <span>Pelawat Hari Ini:</span>
                <span className="font-bold text-blue-400">1,428</span>
              </div>
              <div className="flex justify-between">
                <span>Jumlah Pelawat:</span>
                <span className="font-bold text-emerald-400">384,102</span>
              </div>
              <div className="pt-1 text-[10px] text-slate-500 border-t border-slate-700">
                Kemaskini Terakhir: 10 Ogos 2026
              </div>
            </div>
          </div>
        </div>

        {/* Copyright Bar */}
        <div className="bg-[#0b1329] py-4 border-t border-slate-800/80 text-[11px] text-slate-500 text-center px-4">
          <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>© 2026 Jabatan Perancangan Bandar dan Desa Negeri Perlis (PLANMalaysia Perlis). Hak Cipta Terpelihara.</p>
            <div className="flex gap-4 text-[10px]">
              <span className="hover:underline cursor-pointer">Penafian</span>
              <span>•</span>
              <span className="hover:underline cursor-pointer">Dasar Privasi</span>
              <span>•</span>
              <span className="hover:underline cursor-pointer">Dasar Keselamatan</span>
            </div>
          </div>
        </div>
      </footer>

      {/* Login Modal */}
      <LoginModal 
        isOpen={isLoginModalOpen} 
        onClose={() => setIsLoginModalOpen(false)} 
        onLogin={(u) => {
          setUser(u);
          if (u.role === 'Staff') setActiveTab('staff');
          else if (u.role === 'Pengarah') setActiveTab('pengarah');
          else if (u.role === 'Admin') setActiveTab('admin');
        }}
        users={data.users}
      />

      <Toaster position="top-center" />
    </div>
  );
}
