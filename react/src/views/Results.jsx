import {useEffect, useState} from "react";
import axiosClient from "../axios-client.js";
import H1 from '../components/H1.jsx';


export default function Results() {
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getResults();
  }, [])

  const getResults = () => {
    setLoading(true)
    axiosClient.get('/results')
      .then(({ data }) => {
        setLoading(false)
        setResults(data)
      })
      .catch(() => {
        setLoading(false)
      })
  }

  return (
    <div>
      <H1>Verification Results</H1>
      <div className="card animated fadeInDown">
        <table>
          <thead>
          <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>File type</th>
            <th>Verification result</th>
            <th>Timestamp</th>
          </tr>
          </thead>
          {loading &&
            <tbody>
            <tr>
              <td colSpan={5} className="text-center">
                Loading...
              </td>
            </tr>
            </tbody>
          }
          {!loading &&
            <tbody>
            {results.map((res) => (
              <tr key={res.id}>
                <td>{res.id}</td>
                <td>{res.user_id}</td>
                <td>{res.file_type}</td>
                <td>{res.verification_result}</td>
                <td>{res.created_at}</td>
              </tr>
            ))}
            </tbody>
          }
        </table>
      </div>
    </div>
  )
}
