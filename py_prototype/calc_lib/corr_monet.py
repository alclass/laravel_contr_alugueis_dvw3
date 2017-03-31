#!/usr/bin/env python3


def get_corr_monet_percent_for_month_year(month_year=None):
  return 0.38

class CorrMonetFetcher:

  corr_monet_dict = {
    2015: {
      1: 0.2,
      2: 0.2,
      3: 0.2,
      4: 0.2,
      5: 0.2,
      6: 0.2,
      7: 0.2,
      8: 0.2,
      9: 0.38,
      10: 0.38,
      11: 0.38,
      12: 0.38,
    },
    2016: {
      1: 0.2,
      2: 0.2,
      3: 0.2,
      4: 0.2,
      5: 0.2,
      6: 0.2,
      7: 0.2,
      8: 0.2,
      9: 0.38,
      10: 0.38,
      11: 0.38,
      12: 0.38,
    },
    2017: {
      1: 0.2,
      2: 0.2,
      3: 0.2,
      4: 0.2,
      5: 0.2,
      6: 0.2,
      7: 0.2,
      8: 0.2,
      9: 0.38,
      10: 0.38,
      11: 0.38,
      # 12: 0.38,
    },
  }
  @staticmethod
  def fetch(monthyear):
    year  = monthyear.year
    month = monthyear.month
    try:
      corr_monet = __class__.corr_monet_dict[year][month]
      return corr_monet
    except (AttributeError, KeyError) as e:
      print (e)
      return None

if __name__ == '__main__':
  pass
