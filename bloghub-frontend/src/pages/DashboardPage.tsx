import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { insightsApi, type InsightsPeriodKey, type InsightsResponse } from '../api/client';
import { InfoIcon, ChevronDownIcon } from '../components/icons';
import LoadingPage from '../components/LoadingPage';

const PERIOD_OPTIONS: { value: InsightsPeriodKey; label: string }[] = [
  { value: 'overall', label: 'Overall' },
  { value: 'year', label: 'Year' },
  { value: '6m', label: '6 months' },
  { value: '3m', label: '3 months' },
  { value: '1m', label: '1 month' },
];

function formatEarnings(dollars: number): string {
  return new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(dollars);
}

function StatCard({
  title,
  tooltip,
  children,
  filter,
  onFilterChange,
  className = '',
}: {
  title: string;
  tooltip: string;
  children: React.ReactNode;
  filter?: { value: InsightsPeriodKey; options: typeof PERIOD_OPTIONS };
  onFilterChange?: (value: InsightsPeriodKey) => void;
  className?: string;
}) {
  const [showTooltip, setShowTooltip] = useState(false);

  return (
    <article className={`dashboard-card ${className}`}>
      <header className="dashboard-card-header">
        <div className="dashboard-card-title-row">
          <h3 className="dashboard-card-title">{title}</h3>
          <div
            className="dashboard-card-info-wrap"
            onMouseEnter={() => setShowTooltip(true)}
            onMouseLeave={() => setShowTooltip(false)}
          >
            <button
              type="button"
              className="dashboard-card-info-btn"
              aria-label={tooltip}
            >
              <InfoIcon size={16} />
            </button>
            {showTooltip && (
              <span className="dashboard-card-tooltip" role="tooltip">
                {tooltip}
              </span>
            )}
          </div>
        </div>
        {filter && onFilterChange && (
          <div className="dashboard-card-filter">
            <select
              className="dashboard-card-select"
              value={filter.value}
              onChange={(e) => onFilterChange(e.target.value as InsightsPeriodKey)}
              aria-label={`Filter ${title} by period`}
            >
              {filter.options.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
            <ChevronDownIcon size={14} className="dashboard-card-select-chevron" />
          </div>
        )}
      </header>
      <div className="dashboard-card-body">{children}</div>
    </article>
  );
}

export default function DashboardPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [earningsPeriod, setEarningsPeriod] = useState<InsightsPeriodKey>('1m');
  const [engagementPeriod, setEngagementPeriod] = useState<InsightsPeriodKey>('1m');
  const [data, setData] = useState<InsightsResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError(null);
    insightsApi
      .get()
      .then((res) => {
        if (!cancelled) setData(res);
      })
      .catch((err) => {
        if (!cancelled) setError(err?.message ?? 'Failed to load insights');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, []);

  if (!user && !authLoading) {
    navigate('/login', { replace: true });
    return null;
  }

  if (user && !user.creator_profile?.slug) {
    navigate('/creator/new', { replace: true });
    return null;
  }

  if (authLoading || loading) {
    return <LoadingPage />;
  }

  if (error || !data) {
    return (
      <div className="dashboard-page">
        <h1 className="dashboard-page-title">Insights</h1>
        <div className="dashboard-placeholder-wrap">
          <div className="dashboard-placeholder">
            <p className="dashboard-placeholder-text">
              {error ?? 'Unable to load dashboard data. Please try again later.'}
            </p>
          </div>
        </div>
      </div>
    );
  }

  const earningsAmount = data.earnings[earningsPeriod].amount;
  const engagement = data.engagement[engagementPeriod];
  const { post_views: engagementViews, likes: engagementLikes, comments: engagementComments } = engagement;

  return (
    <div className="dashboard-page">
      <h1 className="dashboard-page-title">Insights</h1>

      <div className="dashboard-cards">
        <StatCard
          title="All members"
          tooltip="Total number of people following (free) or subscribed to (paid)"
          className="dashboard-card-members"
        >
          <div className="dashboard-card-main-value">
            {data.members.total.toLocaleString()}
          </div>
          <div className="dashboard-card-sub-metrics">
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">Subscribers</span>
              <span className="dashboard-card-sub-value">{data.members.paid}</span>
            </span>
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">Followers</span>
              <span className="dashboard-card-sub-value">{data.members.free}</span>
            </span>
          </div>
        </StatCard>

        <StatCard
          title="Earnings"
          tooltip="Total revenue from subscriptions in the selected period"
          filter={{ value: earningsPeriod, options: PERIOD_OPTIONS }}
          onFilterChange={setEarningsPeriod}
          className="dashboard-card-earnings"
        >
          <div className="dashboard-card-main-value">
            {formatEarnings(earningsAmount)}
          </div>
          <div className="dashboard-card-sub-metrics">
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">Revenue</span>
              <span className="dashboard-card-sub-value">{formatEarnings(earningsAmount)}</span>
            </span>
          </div>
        </StatCard>

        <StatCard
          title="Engagement"
          tooltip="Content performance: total post views, likes, and comments in the selected period"
          filter={{ value: engagementPeriod, options: PERIOD_OPTIONS }}
          onFilterChange={setEngagementPeriod}
          className="dashboard-card-engagement"
        >
          <div className="dashboard-card-main-value">
            {engagementViews.toLocaleString()}
          </div>
          <div className="dashboard-card-sub-metrics">
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">Likes</span>
              <span className="dashboard-card-sub-value">{engagementLikes.toLocaleString()}</span>
            </span>
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">Comments</span>
              <span className="dashboard-card-sub-value">{engagementComments.toLocaleString()}</span>
            </span>
          </div>
        </StatCard>

        <StatCard
          title="30-Day Growth"
          tooltip="New paid subscribers and cancellations in the last 30 days"
          className="dashboard-card-growth"
        >
          <div className="dashboard-card-main-value dashboard-card-main-value--growth">
            +{data.growth_30d.new_paid - data.growth_30d.cancellations}
          </div>
          <div className="dashboard-card-sub-metrics">
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">New paid</span>
              <span className="dashboard-card-sub-value dashboard-card-sub-value--positive">
                +{data.growth_30d.new_paid}
              </span>
            </span>
            <span className="dashboard-card-sub-item">
              <span className="dashboard-card-sub-label">Cancellations</span>
              <span className="dashboard-card-sub-value dashboard-card-sub-value--negative">
                −{data.growth_30d.cancellations}
              </span>
            </span>
          </div>
        </StatCard>
      </div>

      <div className="dashboard-placeholder-wrap">
        <div className="dashboard-placeholder">
          <h3 className="dashboard-placeholder-title">Detailed Analytics Coming Soon</h3>
          <p className="dashboard-placeholder-text">
            Advanced reporting features will be presented in the near future: revenue/audience growth over time, engagement trends, user activity, transaction history and more!
          </p>
        </div>
      </div>
    </div>
  );
}
